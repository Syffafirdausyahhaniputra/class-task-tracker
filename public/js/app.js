(() => {
    const apiBase = '/api';
    const tokenKey = 'class-task-tracker-token';

    function getToken() {
        return localStorage.getItem(tokenKey);
    }

    async function apiRequest(path, options = {}) {
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');

        if (options.body !== undefined) {
            headers.set('Content-Type', 'application/json');
        }

        const token = getToken();
        if (token) {
            headers.set('Authorization', `Bearer ${token}`);
        }

        const response = await fetch(`${apiBase}${path}`, {
            ...options,
            headers,
        });

        if (response.status === 401 && document.querySelector('[data-page="dashboard"]')) {
            localStorage.removeItem(tokenKey);
            window.location.assign('/');
        }

        if (response.status === 204) {
            return { data: null };
        }

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const error = new Error(payload.message || 'Permintaan gagal. Coba lagi.');
            error.status = response.status;
            error.errors = payload.errors || {};
            throw error;
        }

        return payload;
    }

    function setNotice(element, message, type = 'info') {
        element.textContent = message;
        element.className = `notice notice-${type}`;
        element.hidden = message === '';
    }

    function renderDeadlineNotice(element, data) {
        element.replaceChildren();
        element.hidden = data.count === 0;
        if (element.hidden) {
            return;
        }

        const message = document.createElement('p');
        message.textContent = `Ada ${data.count} tugas yang mendekati tenggat dalam ${data.days} hari:`;

        const taskList = document.createElement('ul');
        data.tasks.forEach((task) => {
            const item = document.createElement('li');
            item.textContent = `${task.title} (batas waktu: ${formatDueDate(task.due_date)})`;
            taskList.append(item);
        });

        element.append(message, taskList);
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('[data-error-for]').forEach((element) => {
            element.textContent = '';
        });
        form.querySelectorAll('[aria-invalid="true"]').forEach((element) => {
            element.removeAttribute('aria-invalid');
        });
    }

    function showFieldErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const errorElement = form.querySelector(`[data-error-for="${field}"]`);
            const input = form.elements.namedItem(field);
            if (errorElement) {
                errorElement.textContent = messages.join(' ');
            }
            if (input && 'setAttribute' in input) {
                input.setAttribute('aria-invalid', 'true');
            }
        });
    }

    function setFormLoading(form, isLoading, loadingLabel) {
        const submitButton = form.querySelector('[data-submit]');
        if (!submitButton) {
            return;
        }

        submitButton.disabled = isLoading;
        submitButton.querySelector('[data-button-label]').textContent = isLoading
            ? loadingLabel
            : submitButton.dataset.defaultLabel;
    }

    function setDefaultButtonLabels() {
        document.querySelectorAll('[data-submit]').forEach((button) => {
            const label = button.querySelector('[data-button-label]');
            button.dataset.defaultLabel = label.textContent;
        });
    }

    function switchAuthPanel(page, panelName) {
        page.querySelectorAll('[data-auth-tab]').forEach((tab) => {
            const isActive = tab.dataset.authTab === panelName;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });
        page.querySelectorAll('[data-auth-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.authPanel !== panelName;
        });
    }

    function appendClassOptions(select, classes, placeholder) {
        select.replaceChildren();

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        select.append(defaultOption);

        classes.forEach((schoolClass) => {
            const option = document.createElement('option');
            option.value = String(schoolClass.id);
            option.textContent = schoolClass.name;
            select.append(option);
        });
    }

    async function loadClasses(select, placeholder) {
        const response = await apiRequest('/classes');
        appendClassOptions(select, response.data, placeholder);
    }

    function authFormPayload(form) {
        const formData = new FormData(form);
        return Object.fromEntries(formData.entries());
    }

    function initAuthPage(page) {
        if (getToken()) {
            window.location.replace('/dashboard');
            return;
        }

        setDefaultButtonLabels();
        const notice = page.querySelector('[data-notice]');
        const loginForm = page.querySelector('[data-form="login"]');
        const registerForm = page.querySelector('[data-form="register"]');
        const roleSelect = registerForm.elements.namedItem('role');
        const classField = page.querySelector('[data-class-field]');
        const classSelect = registerForm.elements.namedItem('class_id');

        page.querySelectorAll('[data-auth-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                setNotice(notice, '');
                switchAuthPanel(page, tab.dataset.authTab);
            });
        });

        roleSelect.addEventListener('change', () => {
            const needsClass = roleSelect.value === 'student';
            classField.hidden = !needsClass;
            classSelect.required = needsClass;
        });

        loadClasses(classSelect, 'Pilih kelas').catch((error) => {
            setNotice(notice, error.message, 'error');
            classSelect.replaceChildren();
        });

        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFieldErrors(loginForm);
            setNotice(notice, '');
            setFormLoading(loginForm, true, 'Memeriksa...');

            try {
                const payload = authFormPayload(loginForm);
                const response = await apiRequest('/auth/login', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                localStorage.setItem(tokenKey, response.data.token);
                window.location.assign('/dashboard');
            } catch (error) {
                showFieldErrors(loginForm, error.errors || {});
                setNotice(notice, error.message, 'error');
            } finally {
                setFormLoading(loginForm, false);
            }
        });

        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFieldErrors(registerForm);
            setNotice(notice, '');
            setFormLoading(registerForm, true, 'Mendaftarkan...');

            try {
                const payload = authFormPayload(registerForm);
                if (payload.role !== 'student') {
                    delete payload.class_id;
                }

                await apiRequest('/auth/register', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                loginForm.elements.namedItem('email').value = payload.email;
                registerForm.reset();
                classField.hidden = false;
                classSelect.required = true;
                switchAuthPanel(page, 'login');
                setNotice(notice, 'Pendaftaran berhasil. Silakan masuk dengan akun Anda.', 'success');
            } catch (error) {
                showFieldErrors(registerForm, error.errors || {});
                setNotice(notice, error.message, 'error');
            } finally {
                setFormLoading(registerForm, false);
            }
        });
    }

    function formatDueDate(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    }

    function localDateTimeToUtc(value) {
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? value : date.toISOString();
    }

    function createTaskElement(task, role, handlers) {
        const article = document.createElement('article');
        article.className = 'task-card';

        const content = document.createElement('div');
        content.className = 'task-content';

        const title = document.createElement('h3');
        title.textContent = task.title;
        content.append(title);

        if (task.description) {
            const description = document.createElement('p');
            description.className = 'task-description';
            description.textContent = task.description;
            content.append(description);
        }

        const metadata = document.createElement('p');
        metadata.className = 'task-meta';
        metadata.textContent = `Batas waktu: ${formatDueDate(task.due_date)}`;
        content.append(metadata);
        article.append(content);

        const actions = document.createElement('div');
        actions.className = 'task-actions';

        if (role === 'student') {
            const status = document.createElement('span');
            status.className = task.is_done ? 'status-pill status-done' : 'status-pill status-pending';
            status.textContent = task.is_done ? 'Selesai' : 'Belum selesai';

            const completeButton = document.createElement('button');
            completeButton.type = 'button';
            completeButton.className = task.is_done ? 'button button-outline' : 'button button-primary';
            completeButton.textContent = task.is_done ? 'Batalkan' : 'Tandai selesai';
            completeButton.addEventListener('click', () => handlers.toggleCompletion(task));
            actions.append(status, completeButton);
        } else {
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'button button-outline';
            editButton.textContent = 'Ubah';
            editButton.addEventListener('click', () => handlers.edit(task));

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'button button-danger';
            deleteButton.textContent = 'Hapus';
            deleteButton.addEventListener('click', () => handlers.remove(task));
            actions.append(editButton, deleteButton);
        }

        article.append(actions);
        return article;
    }

    function renderTaskList(container, tasks, role, handlers) {
        container.replaceChildren();
        tasks.forEach((task) => {
            container.append(createTaskElement(task, role, handlers));
        });
    }

    function setPagination(page, currentPage, lastPage) {
        const previousButton = page.querySelector('[data-previous]');
        const nextButton = page.querySelector('[data-next]');
        previousButton.disabled = currentPage <= 1;
        nextButton.disabled = currentPage >= lastPage;
        page.querySelector('[data-page-indicator]').textContent = `Halaman ${currentPage} dari ${lastPage}`;
    }

    function localDateTimeValue(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
        return localDate.toISOString().slice(0, 16);
    }

    function initDashboard(page) {
        if (!getToken()) {
            window.location.replace('/');
            return;
        }

        setDefaultButtonLabels();
        const notice = page.querySelector('[data-notice]');
        const deadlineNotice = page.querySelector('[data-deadline-notice]');
        const taskList = page.querySelector('[data-task-list]');
        const loadingMessage = page.querySelector('[data-loading]');
        const emptyMessage = page.querySelector('[data-empty]');
        const filterForm = page.querySelector('[data-form="filters"]');
        const taskForm = page.querySelector('[data-form="task"]');
        const taskEditor = page.querySelector('[data-task-editor]');
        const newTaskButton = page.querySelector('[data-new-task]');
        const taskClassSelect = taskForm.elements.namedItem('class_id');
        let currentUser = null;
        let pageNumber = 1;
        let lastPage = 1;
        let currentTaskId = null;

        async function loadTasks() {
            loadingMessage.hidden = false;
            loadingMessage.textContent = 'Memuat...';
            emptyMessage.hidden = true;
            taskList.replaceChildren();
            setNotice(notice, '');

            const params = new URLSearchParams();
            const filterData = new FormData(filterForm);
            const search = filterData.get('search').trim();
            const status = filterData.get('status');
            if (search) {
                params.set('search', search);
            }
            if (status) {
                params.set('status', status);
            }
            params.set('page', String(pageNumber));
            params.set('per_page', '5');

            try {
                const response = await apiRequest(`/tasks?${params.toString()}`);
                lastPage = response.meta.last_page;
                renderTaskList(taskList, response.data, currentUser.role, {
                    toggleCompletion: async (task) => {
                        await toggleCompletion(task);
                    },
                    edit: (task) => openEditor(task),
                    remove: async (task) => {
                        await deleteTask(task);
                    },
                });
                emptyMessage.hidden = response.data.length !== 0;
                setPagination(page, response.meta.current_page, response.meta.last_page);
            } catch (error) {
                setNotice(notice, error.message, 'error');
            } finally {
                loadingMessage.hidden = true;
            }
        }

        function resetTaskEditor() {
            currentTaskId = null;
            taskForm.reset();
            clearFieldErrors(taskForm);
            page.querySelector('[data-editor-title]').textContent = 'Tugas baru';
            taskForm.querySelector('[data-button-label]').textContent = 'Simpan tugas';
            taskForm.querySelector('[data-submit]').dataset.defaultLabel = 'Simpan tugas';
        }

        function openEditor(task = null) {
            resetTaskEditor();
            taskEditor.hidden = false;

            if (task) {
                currentTaskId = task.id;
                page.querySelector('[data-editor-title]').textContent = 'Ubah tugas';
                taskForm.querySelector('[data-button-label]').textContent = 'Simpan perubahan';
                taskForm.querySelector('[data-submit]').dataset.defaultLabel = 'Simpan perubahan';
                taskForm.elements.namedItem('title').value = task.title;
                taskForm.elements.namedItem('description').value = task.description || '';
                taskForm.elements.namedItem('due_date').value = localDateTimeValue(task.due_date);
                taskClassSelect.value = String(task.class_id);
            }

            taskEditor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        async function toggleCompletion(task) {
            const method = task.is_done ? 'DELETE' : 'POST';
            try {
                await apiRequest(`/tasks/${task.id}/complete`, { method });
                await loadTasks();
                await loadDeadlineNotice();
            } catch (error) {
                setNotice(notice, error.message, 'error');
            }
        }

        async function loadDeadlineNotice() {
            deadlineNotice.replaceChildren();
            deadlineNotice.hidden = true;

            const response = await apiRequest('/tasks/upcoming-deadlines');
            renderDeadlineNotice(deadlineNotice, response.data);
        }

        async function deleteTask(task) {
            if (!window.confirm(`Hapus tugas "${task.title}"?`)) {
                return;
            }

            try {
                await apiRequest(`/tasks/${task.id}`, { method: 'DELETE' });
                await loadTasks();
                await loadDeadlineNotice();
                setNotice(notice, 'Tugas berhasil dihapus.', 'success');
            } catch (error) {
                setNotice(notice, error.message, 'error');
            }
        }

        taskForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFieldErrors(taskForm);
            setNotice(notice, '');
            setFormLoading(taskForm, true, 'Menyimpan...');

            const payload = authFormPayload(taskForm);
            payload.description = payload.description || null;
            payload.due_date = localDateTimeToUtc(payload.due_date);
            payload.class_id = Number(payload.class_id);
            const isEditing = currentTaskId !== null;
            const path = isEditing ? `/tasks/${currentTaskId}` : '/tasks';
            const method = isEditing ? 'PUT' : 'POST';

            try {
                await apiRequest(path, {
                    method,
                    body: JSON.stringify(payload),
                });
                taskEditor.hidden = true;
                resetTaskEditor();
                pageNumber = 1;
                await loadTasks();
                await loadDeadlineNotice();
                setNotice(notice, isEditing ? 'Tugas berhasil diperbarui.' : 'Tugas berhasil dibuat.', 'success');
            } catch (error) {
                showFieldErrors(taskForm, error.errors || {});
                setNotice(notice, error.message, 'error');
            } finally {
                setFormLoading(taskForm, false);
            }
        });

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            pageNumber = 1;
            loadTasks();
        });

        page.querySelector('[data-previous]').addEventListener('click', () => {
            if (pageNumber > 1) {
                pageNumber -= 1;
                loadTasks();
            }
        });
        page.querySelector('[data-next]').addEventListener('click', () => {
            if (pageNumber < lastPage) {
                pageNumber += 1;
                loadTasks();
            }
        });
        newTaskButton.addEventListener('click', () => openEditor());
        page.querySelector('[data-cancel-edit]').addEventListener('click', () => {
            taskEditor.hidden = true;
            resetTaskEditor();
        });

        page.querySelector('[data-logout]').addEventListener('click', async () => {
            try {
                await apiRequest('/auth/logout', { method: 'POST' });
            } catch (error) {
                setNotice(notice, error.message, 'error');
                return;
            }

            localStorage.removeItem(tokenKey);
            window.location.assign('/');
        });

        async function loadDashboard() {
            try {
                const response = await apiRequest('/auth/me');
                currentUser = response.data;
                page.querySelector('[data-user-name]').textContent = currentUser.name;
                page.querySelector('[data-user-role]').textContent = currentUser.role === 'teacher' ? 'Guru' : 'Siswa';
                page.querySelector('[data-heading]').textContent = currentUser.role === 'teacher'
                    ? 'Tugas yang Anda buat'
                    : 'Tugas kelas Anda';
                page.querySelector('[data-subheading]').textContent = currentUser.role === 'teacher'
                    ? 'Kelola tugas untuk kelas-kelas Anda.'
                    : 'Lihat tugas kelas dan perbarui status penyelesaian.';

                if (currentUser.role === 'teacher') {
                    newTaskButton.hidden = false;
                    await loadClasses(taskClassSelect, 'Pilih kelas');
                    taskEditor.hidden = true;
                } else {
                    taskEditor.remove();
                    newTaskButton.remove();
                }

                await loadTasks();
                await loadDeadlineNotice();
            } catch (error) {
                setNotice(notice, error.message, 'error');
            }
        }

        loadDashboard();
    }

    const authPage = document.querySelector('[data-page="auth"]');
    if (authPage) {
        initAuthPage(authPage);
    }

    const dashboardPage = document.querySelector('[data-page="dashboard"]');
    if (dashboardPage) {
        initDashboard(dashboardPage);
    }
})();
