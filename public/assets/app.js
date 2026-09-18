
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

const initScheduleControls = () => {
    const controls = document.querySelector('[data-schedule-controls]');
    const toggle = document.querySelector('[data-schedule-controls-toggle]');
    if (!controls || !toggle) return;

    const storageKey = 'nsmk-schedule-controls-collapsed';
    const applyState = (collapsed) => {
        controls.classList.toggle('is-collapsed', collapsed);
        toggle.textContent = collapsed ? 'Открыть панель' : 'Скрыть панель';
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    };

    applyState(localStorage.getItem(storageKey) === '1');

    toggle.addEventListener('click', () => {
        const collapsed = !controls.classList.contains('is-collapsed');
        applyState(collapsed);
        localStorage.setItem(storageKey, collapsed ? '1' : '0');
    });
};

const initSchedule = () => {
    const board = document.querySelector('[data-schedule-board]');
    if (!board) return;

    const canEdit = board.dataset.canEdit === '1';
    const status = document.querySelector('[data-drag-status]');
    const setStatus = (text) => { if (status) status.textContent = text; };

    const showToast = (message, kind = 'success') => {
        let toast = document.querySelector('[data-app-toast]');
        if (!toast) {
            toast = document.createElement('div');
            toast.dataset.appToast = '1';
            toast.className = 'app-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.className = `app-toast app-toast--${kind}`;
        requestAnimationFrame(() => toast.classList.add('is-visible'));
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(() => toast.classList.remove('is-visible'), 1800);
    };

    // Сворачивание дней: управляем именно классом секции, без зависимости от
    // высоты строк или overflow расписания.
    document.querySelectorAll('[data-day-toggle]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const section = button.closest('[data-day-block]');
            if (!section) return;
            const collapsed = !section.classList.contains('is-collapsed');
            section.classList.toggle('is-collapsed', collapsed);
            const label = button.querySelector('span');
            if (label) label.textContent = collapsed ? 'Развернуть' : 'Свернуть';
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });
    });

    const search = document.querySelector('#schedule-search');
    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        document.querySelectorAll('.schedule-row').forEach((row) => {
            const cells = [...row.querySelectorAll('[data-drop-zone]')];
            const match = !query || cells.some((cell) => (cell.dataset.searchText || '').toLowerCase().includes(query));
            row.classList.toggle('is-search-hidden', Boolean(query) && !match);
        });
    });

    const teacherSearch = document.querySelector('#teacher-search');
    teacherSearch?.addEventListener('input', () => {
        const query = teacherSearch.value.trim().toLowerCase();
        document.querySelectorAll('[data-teacher-resource]').forEach((item) => {
            item.classList.toggle('is-search-hidden', Boolean(query) && !(item.dataset.teacherSearch || '').toLowerCase().includes(query));
        });
    });

    initResourcePanel();
    if (!canEdit) return;

    const dropZones = [...board.querySelectorAll('[data-drop-zone]')];
    let drag = null;
    let pending = null;
    let pointer = { x: 0, y: 0 };
    let autoScrollFrame = 0;
    let suppressClickUntil = 0;
    let currentDropZone = null;

    const clearTargets = () => dropZones.forEach((zone) => zone.classList.remove('is-drop-target'));

    const zoneAtPoint = (x, y) => {
        const element = document.elementFromPoint(x, y);
        return element?.closest?.('[data-drop-zone]') || null;
    };

    const removeEmpty = (zone) => zone?.querySelectorAll('.empty-cell').forEach((element) => element.remove());

    const restoreEmpty = (zone) => {
        if (!zone || zone.querySelector('[data-lesson-card]')) return;
        removeEmpty(zone);
        const addUrl = zone.dataset.emptyUrl;
        const element = document.createElement(addUrl ? 'a' : 'div');
        element.className = 'empty-cell';
        element.textContent = addUrl ? 'Добавить' : '—';
        if (addUrl) element.href = addUrl;
        zone.appendChild(element);
    };

    const normalizeZone = (zone) => {
        if (!zone) return;
        if (zone.querySelector('[data-lesson-card]')) removeEmpty(zone);
        else restoreEmpty(zone);
    };

    const stopAutoScroll = () => {
        if (autoScrollFrame) cancelAnimationFrame(autoScrollFrame);
        autoScrollFrame = 0;
    };

    // Плавная автопрокрутка: скорость намеренно небольшая.
    const autoScroll = () => {
        autoScrollFrame = 0;
        if (!drag) return;
        const rect = board.getBoundingClientRect();
        const edge = 70;
        const maxSpeed = 3;
        let dx = 0;
        let dy = 0;

        if (pointer.x < rect.left + edge) {
            dx = -Math.max(1, Math.round((rect.left + edge - pointer.x) / edge * maxSpeed));
        } else if (pointer.x > rect.right - edge) {
            dx = Math.max(1, Math.round((pointer.x - (rect.right - edge)) / edge * maxSpeed));
        }
        if (pointer.y < rect.top + edge) {
            dy = -Math.max(1, Math.round((rect.top + edge - pointer.y) / edge * maxSpeed));
        } else if (pointer.y > rect.bottom - edge) {
            dy = Math.max(1, Math.round((pointer.y - (rect.bottom - edge)) / edge * maxSpeed));
        }

        if (dx || dy) {
            board.scrollLeft += dx;
            board.scrollTop += dy;
            // Цель меняем только если указатель реально сдвинулся; сама прокрутка
            // не должна каждый кадр заново искать все зоны.
            autoScrollFrame = requestAnimationFrame(autoScroll);
        }
    };

    const scheduleAutoScroll = () => {
        if (drag && !autoScrollFrame) autoScrollFrame = requestAnimationFrame(autoScroll);
    };

    const updateTarget = () => {
        if (!drag) return;
        const zone = zoneAtPoint(pointer.x, pointer.y);
        if (zone !== currentDropZone) {
            currentDropZone?.classList.remove('is-drop-target');
            currentDropZone = zone;
            currentDropZone?.classList.add('is-drop-target');
        }
        drag.currentZone = currentDropZone;
        scheduleAutoScroll();
    };

    const createGhost = (item) => {
        const ghost = item.cloneNode(true);
        ghost.classList.add('drag-ghost');
        ghost.removeAttribute('draggable');
        ghost.removeAttribute('id');
        ghost.querySelectorAll?.('[id]').forEach((node) => node.removeAttribute('id'));
        ghost.style.position = 'fixed';
        ghost.style.left = '0';
        ghost.style.top = '0';
        ghost.style.margin = '0';
        ghost.style.width = `${Math.min(item.getBoundingClientRect().width, 330)}px`;
        ghost.style.height = 'auto';
        ghost.style.display = 'grid';
        document.body.appendChild(ghost);
        return ghost;
    };

    const moveGhost = () => {
        if (!drag?.ghost) return;
        drag.ghost.style.transform = `translate3d(${pointer.x + 12}px, ${pointer.y + 12}px, 0) rotate(1deg)`;
    };

    const beginDrag = (item, type, sourceZone, pointerId) => {
        if (drag) return;
        const openTeachers = [...document.querySelectorAll('.teacher-resource')].filter((el) => el.open);
        drag = {
            item,
            type,
            sourceZone,
            pointerId,
            openTeachers,
            ghost: createGhost(item),
        };
        item.classList.add('is-dragging');
        item.setPointerCapture?.(pointerId);
        document.body.classList.add('is-schedule-dragging');
        moveGhost();
        updateTarget();
    };

    const cleanupDrag = () => {
        stopAutoScroll();
        clearTargets();
        currentDropZone = null;
        if (!drag) return;
        const current = drag;
        current.item.classList.remove('is-dragging');
        current.ghost?.remove();
        try { current.item.releasePointerCapture?.(current.pointerId); } catch (_) {}
        current.openTeachers?.forEach((teacher) => { teacher.open = true; });
        drag = null;
        document.body.classList.remove('is-schedule-dragging');
    };

    const truncateNote = (value, max = 15) => {
        const text = String(value ?? '').trim();
        return text.length > max ? `${text.slice(0, max)}…` : text;
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>\"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[char]));

    const buildLessonCard = (lesson, createUrl) => {
        const article = document.createElement('article');
        article.className = 'lesson-card';
        article.dataset.lessonCard = '1';
        article.dataset.lessonId = lesson.id;
        article.dataset.teacherId = lesson.teacher.id;
        article.dataset.subjectId = lesson.subject.id;
        article.dataset.moveUrl = lesson.move_url;
        article.dataset.noteUrl = lesson.note_url;
        article.dataset.createUrl = createUrl || '';
        article.dataset.week1 = lesson.week1 ?? '';
        article.dataset.week2 = lesson.week2 ?? '';
        article.style.setProperty('--teacher-color', lesson.teacher.color);

        const body = document.createElement('button');
        body.type = 'button';
        body.className = 'lesson-card__body lesson-note-trigger';
        body.dataset.note = lesson.note || '';
        body.dataset.subject = lesson.subject.name;
        body.dataset.teacher = lesson.teacher.name;
        body.dataset.room = lesson.classroom || '—';
        body.innerHTML = `
            <div class="lesson-topline"><span class="lesson-type">${escapeHtml(lesson.lesson_type)}</span><span class="lesson-room">${escapeHtml(lesson.classroom || '—')}</span></div>
            <strong>${escapeHtml(lesson.subject.short_name || lesson.subject.name)}</strong>
            <span class="lesson-subject">${escapeHtml(lesson.subject.name)}</span>
            <span class="lesson-teacher">${escapeHtml(lesson.teacher.name)}</span>
            <span class="lesson-weeks"><span>1 нед.: ${escapeHtml(lesson.week1 ?? '—')}</span><i></i><span>2 нед.: ${escapeHtml(lesson.week2 ?? '—')}</span></span>
            ${lesson.note ? `<span class="lesson-note-preview" title="${escapeHtml(lesson.note)}">${escapeHtml(truncateNote(lesson.note))}</span>` : ''}`;
        article.appendChild(body);

        const actions = document.createElement('div');
        actions.className = 'lesson-actions';
        const edit = document.createElement('a');
        edit.href = lesson.edit_url; edit.className = 'button button--small'; edit.textContent = 'Изменить';
        const form = document.createElement('form');
        form.method = 'POST'; form.action = lesson.delete_url;
        form.innerHTML = `<input type="hidden" name="_token" value="${escapeHtml(csrfToken)}"><input type="hidden" name="_method" value="DELETE">`;
        const del = document.createElement('button');
        del.type = 'submit'; del.className = 'button button--small button--danger'; del.textContent = 'Удалить';
        del.addEventListener('click', (event) => { if (!confirm('Удалить занятие?')) event.preventDefault(); });
        form.appendChild(del); actions.append(edit, form); article.appendChild(actions);
        body.addEventListener('click', () => {
            const modal = document.querySelector('[data-note-modal]');
            if (!modal) return;
            const input = modal.querySelector('[data-note-input]');
            modal.querySelector('[data-note-subject]').textContent = body.dataset.subject || '';
            modal.querySelector('[data-note-teacher]').textContent = body.dataset.teacher || '';
            modal.querySelector('[data-note-room]').textContent = body.dataset.room || '—';
            if (input) input.value = body.dataset.note || '';
            modal.dataset.dynamicCardId = String(lesson.id);
            modal.setAttribute('aria-hidden','false'); modal.classList.add('is-open'); input?.focus();
        });
        return article;
    };

    const performDrop = async (zone) => {
        if (!drag || !zone) {
            cleanupDrag();
            return;
        }

        const current = drag;
        const week = Number(board.dataset.selectedWeek);
        const pair = Number(zone.dataset.pair);
        const day = zone.dataset.day;
        const groupId = zone.dataset.groupId;
        const oldZone = current.sourceZone;

        try {
            if (current.type === 'lesson') {
                if (zone === oldZone) return;

                const payload = new URLSearchParams({
                    _token: csrfToken,
                    _method: 'PATCH',
                    group_id: groupId,
                    day,
                    pair: String(pair),
                    week: String(week),
                });
                const targetBefore = zone.querySelector('[data-lesson-card]');
                if (targetBefore && targetBefore !== current.item) {
                    payload.append('swap_lesson_id', targetBefore.dataset.lessonId);
                }

                const response = await fetch(current.item.dataset.moveUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: payload,
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Не удалось сохранить перенос.');
                }

                // DOM меняем только после успешного ответа сервера.
                if (result.swapped && targetBefore && targetBefore !== current.item) {
                    oldZone?.replaceChildren();
                    zone.replaceChildren();
                    oldZone?.appendChild(targetBefore);
                    zone.appendChild(current.item);
                } else {
                    zone.replaceChildren();
                    zone.appendChild(current.item);
                    oldZone?.replaceChildren();
                }
                normalizeZone(oldZone);
                normalizeZone(zone);
                showToast(result.swapped ? 'Занятия поменялись местами.' : 'Занятие перенесено.');
            } else {
                const payload = new URLSearchParams({
                    _token: csrfToken,
                    group_id: groupId,
                    'day[]': day,
                    teacher_id: current.item.dataset.teacherId,
                    subject_id: current.item.dataset.subjectId,
                    lesson_type: 'Лекция',
                });
                payload.append(week === 1 ? 'week1_lesson[]' : 'week2_lesson[]', String(pair));

                // Показываем занятие сразу. Пользователь не должен ждать HTTP-запрос,
                // чтобы увидеть результат броска. При ошибке возвращаем исходную ячейку.
                // Сразу показываем полноценную карточку. HTTP-запрос идёт в фоне,
                // поэтому HDD/медленный сервер не создаёт задержку в интерфейсе.
                const optimistic = document.createElement('article');
                optimistic.className = 'lesson-card lesson-card--pending';
                optimistic.style.setProperty('--teacher-color', current.item.dataset.teacherColor || '#2563eb');
                const optimisticBody = document.createElement('div');
                optimisticBody.className = 'lesson-card__body';
                const subjectShort = current.item.querySelector('strong')?.textContent || '';
                const subjectName = current.item.querySelector('span')?.textContent || subjectShort;
                const teacherName = current.item.closest('[data-teacher-resource]')?.querySelector('.teacher-name')?.textContent || '';
                optimisticBody.innerHTML = `<div class="lesson-topline"><span class="lesson-type">Лекция</span><span class="lesson-room">—</span></div><strong>${escapeHtml(subjectShort)}</strong><span class="lesson-subject">${escapeHtml(subjectName)}</span><span class="lesson-teacher">${escapeHtml(teacherName)}</span><span class="lesson-weeks"><span>${week === 1 ? '1 нед.' : '2 нед.'}: ${pair}</span></span>`;
                optimistic.appendChild(optimisticBody);
                zone.replaceChildren(optimistic);
                normalizeZone(zone);
                showToast('Занятие добавляется…');

                try {
                    const response = await fetch(current.item.dataset.createUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        },
                        body: payload,
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Не удалось добавить занятие.');
                    }
                    const lesson = result.lesson;
                    if (!lesson) throw new Error('Сервер не вернул созданное занятие.');
                    const card = buildLessonCard(lesson, current.item.dataset.createUrl);
                    zone.replaceChildren(card);
                    normalizeZone(zone);
                    showToast('Занятие добавлено в расписание.');
                } catch (error) {
                    restoreEmpty(zone);
                    throw error;
                }
                return;
            }
        } catch (error) {
            showToast(error.message || 'Ошибка операции.', 'error');
        } finally {
            cleanupDrag();
        }
    };

    const pointerDown = (event) => {
        if (event.button !== 0 || drag || pending) return;
        const target = event.target instanceof Element ? event.target : null;
        const lesson = target?.closest('[data-lesson-card]');
        const subject = target?.closest('[data-subject-source]');
        const item = lesson || subject;
        if (!item) return;
        if (lesson && target.closest('.lesson-actions, a, form')) return;

        pointer = { x: event.clientX, y: event.clientY };
        pending = {
            item,
            pointerId: event.pointerId,
            x: event.clientX,
            y: event.clientY,
            sourceZone: item.closest('[data-drop-zone]'),
            teacherDetails: subject?.closest('[data-teacher-resource]') || null,
        };
        if (pending.teacherDetails) pending.teacherDetails.open = true;
        // Для дисциплины начинаем перетаскивание практически сразу — пользователь
        // должен видеть карточку прямо под курсором.
        if (subject) {
            pending = null;
            beginDrag(item, 'subject', null, event.pointerId);
        }
        // Отключаем нативный HTML5 drag: всё управление делает Pointer Events.
        event.preventDefault();
    };

    const pointerMove = (event) => {
        pointer = { x: event.clientX, y: event.clientY };
        if (!drag && pending) {
            const dx = event.clientX - pending.x;
            const dy = event.clientY - pending.y;
            if (Math.hypot(dx, dy) >= 3) {
                const current = pending;
                pending = null;
                beginDrag(
                    current.item,
                    current.item.matches('[data-lesson-card]') ? 'lesson' : 'subject',
                    current.sourceZone,
                    current.pointerId,
                );
            }
        }
        if (drag) {
            moveGhost();
            updateTarget();
        }
    };

    const pointerUp = (event) => {
        pointer = { x: event.clientX, y: event.clientY };
        pending = null;
        if (!drag) return;
        suppressClickUntil = Date.now() + 300;
        performDrop(drag.currentZone || zoneAtPoint(pointer.x, pointer.y));
    };

    const pointerCancel = () => {
        pending = null;
        if (drag) cleanupDrag();
    };

    document.addEventListener('click', (event) => {
        if (!drag && !pending) return;
        const subject = event.target instanceof Element ? event.target.closest('[data-subject-source]') : null;
        if (subject) { event.preventDefault(); event.stopPropagation(); }
    }, true);

    document.addEventListener('pointerdown', pointerDown, true);
    document.addEventListener('pointermove', pointerMove, true);
    document.addEventListener('pointerup', pointerUp, true);
    document.addEventListener('pointercancel', pointerCancel, true);

    document.addEventListener('click', (event) => {
        if (Date.now() < suppressClickUntil) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('wheel', (event) => {
        if (!drag) return;
        const rect = board.getBoundingClientRect();
        const insideBoard = event.clientX >= rect.left && event.clientX <= rect.right && event.clientY >= rect.top && event.clientY <= rect.bottom;
        if (!insideBoard) return;
        event.preventDefault();
        if (event.shiftKey || Math.abs(event.deltaX) > Math.abs(event.deltaY)) {
            board.scrollLeft += event.deltaX || event.deltaY;
        } else {
            board.scrollTop += event.deltaY;
        }
        updateTarget();
    }, { passive: false, capture: true });
};

const initResourcePanel = () => {
    const wrap = document.querySelector('[data-resource-wrap]');
    if (!wrap) return;
    const toggle = wrap.querySelector('[data-resource-toggle]');
    const storageKey = 'nsmk-resource-panel-collapsed-v5';

    const apply = (collapsed) => {
        wrap.classList.toggle('is-collapsed', collapsed);
        if (toggle) {
            toggle.textContent = collapsed ? '‹' : '›';
            toggle.setAttribute('aria-label', collapsed ? 'Открыть преподавателей и дисциплины' : 'Скрыть преподавателей и дисциплины');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }
    };

    // Новая версия ключа специально игнорирует старое состояние, которое могло
    // оставить панель навсегда скрытой.
    apply(localStorage.getItem(storageKey) === '1');
    const togglePanel = () => {
        const collapsed = !wrap.classList.contains('is-collapsed');
        apply(collapsed);
        localStorage.setItem(storageKey, collapsed ? '1' : '0');
    };
    toggle?.addEventListener('click', togglePanel);
};

const initLessonForm = () => {
    const teacher = document.querySelector('[data-teacher-select]');
    const subject = document.querySelector('[data-subject-select]');
    if (!teacher || !subject) return;
    const update = () => {
        const id = teacher.value;
        [...subject.options].forEach((option, index) => {
            if (index === 0) { option.hidden = false; return; }
            const teachers = (option.dataset.teachers || '').split(',').filter(Boolean);
            option.hidden = !id || !teachers.includes(id);
        });
        subject.disabled = !id;
        if (subject.selectedOptions[0]?.hidden) subject.value = '';
    };
    teacher.addEventListener('change', update); update();
};

const initRoleForm = () => {
    const role = document.querySelector('[data-role-select]');
    if (!role) return;
    const update = () => {
        document.querySelectorAll('[data-profile-field]').forEach((field) => {
            const active = field.dataset.profileField === role.value;
            field.style.display = active ? 'grid' : 'none';
            const control = field.querySelector('select');
            if (control) control.disabled = !active;
        });
    };
    role.addEventListener('change', update); update();
};

const initNotes = () => {
    const modal = document.querySelector('[data-note-modal]');
    if (!modal) return;
    const input = modal.querySelector('[data-note-input]');
    const save = modal.querySelector('[data-note-save]');
    let currentUrl = null;
    let currentCard = null;
    document.querySelectorAll('[data-note-close]').forEach((el) => el.addEventListener('click', () => { modal.setAttribute('aria-hidden','true'); modal.classList.remove('is-open'); }));
    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('.lesson-note-trigger') : null;
        if (!button) return;
        currentCard = button.closest('[data-lesson-card]'); currentUrl = currentCard?.dataset.noteUrl || null;
        modal.querySelector('[data-note-subject]').textContent = button.dataset.subject || '';
        modal.querySelector('[data-note-teacher]').textContent = button.dataset.teacher || '';
        modal.querySelector('[data-note-room]').textContent = button.dataset.room || '—';
        if (input) input.value = button.dataset.note || '';
        modal.setAttribute('aria-hidden','false'); modal.classList.add('is-open'); input?.focus();
    });
    if (save) save.addEventListener('click', async () => {
        if (!currentUrl) return;
        const status = modal.querySelector('[data-note-status]');
        status.textContent = 'Сохраняем…';
        const response = await fetch(currentUrl, { method:'PATCH', headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'}, body:JSON.stringify({note:input.value}) });
        const result = await response.json();
        if (!response.ok) { status.textContent = result.message || 'Ошибка сохранения.'; return; }
        const button = currentCard?.querySelector('.lesson-note-trigger');
        if (button) { button.dataset.note = result.note || ''; if (result.note) { if (!button.querySelector('.lesson-note-indicator')) { const mark=document.createElement('span'); mark.className='lesson-note-indicator'; mark.textContent='Есть заметка'; button.append(mark); } } else { button.querySelector('.lesson-note-indicator')?.remove(); } }
        status.textContent = 'Заметка сохранена.';
    });
};

const initTheme = () => {
    const root = document.documentElement;
    const toggle = document.querySelector('[data-theme-toggle]');
    const storageKey = 'nsmk-theme';
    const apply = (theme) => {
        const dark = theme === 'dark';
        root.dataset.theme = dark ? 'dark' : 'light';
        if (toggle) {
            toggle.textContent = dark ? 'Светлая тема' : 'Тёмная тема';
            toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
        }
    };

    apply(localStorage.getItem(storageKey) === 'dark' ? 'dark' : 'light');
    toggle?.addEventListener('click', () => {
        const dark = root.dataset.theme !== 'dark';
        apply(dark ? 'dark' : 'light');
        localStorage.setItem(storageKey, dark ? 'dark' : 'light');
    });
};

window.addEventListener('DOMContentLoaded', () => { initTheme(); initScheduleControls(); initSchedule(); initLessonForm(); initRoleForm(); initNotes(); });
