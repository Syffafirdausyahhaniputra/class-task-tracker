@extends('layouts.app')

@section('title', 'Dashboard | Class Task Tracker')

@section('content')
<section class="dashboard" data-page="dashboard">
    <header class="topbar">
        <a class="brand" href="/dashboard">Class Task Tracker</a>
        <div class="user-menu">
            <div class="user-summary">
                <span class="user-name" data-user-name>Memuat akun...</span>
                <span class="user-role" data-user-role></span>
            </div>
            <button class="button button-outline" type="button" data-logout>Keluar</button>
        </div>
    </header>

    <section class="dashboard-heading">
        <div>
            <p class="eyebrow">DASHBOARD</p>
            <h1 data-heading>Tugas kelas</h1>
            <p class="muted" data-subheading>Daftar tugas Anda akan tampil di sini.</p>
        </div>
        <button class="button button-primary" type="button" data-new-task hidden>Tambah tugas</button>
    </section>

    <div class="notice" data-notice role="status" aria-live="polite" hidden></div>

    <section class="panel task-editor" data-task-editor hidden>
        <div class="section-heading">
            <div>
                <h2 data-editor-title>Tugas baru</h2>
                <p class="muted">Isi detail tugas untuk kelas yang dipilih.</p>
            </div>
            <button class="button button-quiet" type="button" data-cancel-edit>Tutup</button>
        </div>
        <form data-form="task" novalidate>
            <div class="form-grid">
                <div class="field-span">
                    <label for="task-title">Judul</label>
                    <input id="task-title" name="title" type="text" maxlength="255" required>
                    <small class="field-error" data-error-for="title"></small>
                </div>

                <div class="field-span">
                    <label for="task-description">Deskripsi</label>
                    <textarea id="task-description" name="description" rows="3"></textarea>
                    <small class="field-error" data-error-for="description"></small>
                </div>

                <div>
                    <label for="task-due-date">Batas waktu</label>
                    <input id="task-due-date" name="due_date" type="datetime-local" required>
                    <small class="field-error" data-error-for="due_date"></small>
                </div>

                <div>
                    <label for="task-class">Kelas</label>
                    <select id="task-class" name="class_id" required>
                        <option value="">Pilih kelas</option>
                    </select>
                    <small class="field-error" data-error-for="class_id"></small>
                </div>
            </div>
            <div class="form-actions">
                <button class="button button-primary" type="submit" data-submit>
                    <span data-button-label>Simpan tugas</span>
                </button>
            </div>
        </form>
    </section>

    <section class="panel task-panel">
        <form class="task-filters" data-form="filters">
            <div class="search-field">
                <label for="task-search">Cari judul tugas</label>
                <input id="task-search" name="search" type="search" placeholder="Contoh: matematika">
            </div>
            <div class="status-field">
                <label for="task-status">Status</label>
                <select id="task-status" name="status">
                    <option value="">Semua status</option>
                    <option value="done">Selesai</option>
                    <option value="pending">Belum selesai</option>
                </select>
            </div>
            <button class="button button-secondary" type="submit">Cari</button>
        </form>

        <p class="loading-message" data-loading role="status" aria-live="polite">Memuat...</p>
        <p class="empty-message" data-empty hidden>Belum ada tugas yang cocok.</p>
        <div class="task-list" data-task-list aria-live="polite"></div>

        <nav class="pagination" aria-label="Paginasi tugas">
            <button class="button button-outline" type="button" data-previous disabled>Sebelumnya</button>
            <span class="page-indicator" data-page-indicator></span>
            <button class="button button-outline" type="button" data-next disabled>Berikutnya</button>
        </nav>
    </section>
</section>
@endsection
