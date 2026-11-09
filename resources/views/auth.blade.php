@extends('layouts.app')

@section('title', 'Masuk | Class Task Tracker')

@section('content')
<section class="auth-layout" data-page="auth">
    <div class="auth-intro">
        <p class="eyebrow">CLASS TASK TRACKER</p>
        <h1>Belajar lebih teratur, satu tugas setiap kali.</h1>
        <p class="muted">Tempat guru membagikan tugas dan siswa memantau progres kelas.</p>
    </div>

    <div class="panel auth-panel">
        <div class="auth-tabs" role="tablist" aria-label="Pilih akses">
            <button class="tab-button is-active" id="login-tab" type="button" role="tab"
                aria-selected="true" aria-controls="login-panel" data-auth-tab="login">Masuk</button>
            <button class="tab-button" id="register-tab" type="button" role="tab"
                aria-selected="false" aria-controls="register-panel" data-auth-tab="register">Daftar</button>
        </div>

        <div class="notice" data-notice role="status" aria-live="polite" hidden></div>

        <section id="login-panel" role="tabpanel" aria-labelledby="login-tab" data-auth-panel="login">
            <h2>Selamat datang kembali</h2>
            <p class="muted">Masuk menggunakan akun sekolah Anda.</p>
            <form data-form="login" novalidate>
                <label for="login-email">Email</label>
                <input id="login-email" name="email" type="email" autocomplete="email" required>
                <small class="field-error" data-error-for="email"></small>

                <label for="login-password">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" required>
                <small class="field-error" data-error-for="password"></small>

                <button class="button button-primary button-full" type="submit" data-submit>
                    <span data-button-label>Masuk</span>
                </button>
            </form>
        </section>

        <section id="register-panel" role="tabpanel" aria-labelledby="register-tab" data-auth-panel="register" hidden>
            <h2>Buat akun</h2>
            <p class="muted">Daftar sebagai siswa untuk melihat tugas kelas Anda.</p>
            <form data-form="register" novalidate>
                <label for="register-name">Nama</label>
                <input id="register-name" name="name" type="text" autocomplete="name" required>
                <small class="field-error" data-error-for="name"></small>

                <label for="register-email">Email</label>
                <input id="register-email" name="email" type="email" autocomplete="email" required>
                <small class="field-error" data-error-for="email"></small>

                <label for="register-password">Password (minimal 8 karakter)</label>
                <input id="register-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                <small class="field-error" data-error-for="password"></small>

                <p class="registration-role"><strong>Peran:</strong> Siswa</p>
                <input name="role" type="hidden" value="student">
                <small class="field-error" data-error-for="role"></small>

                <div data-class-field>
                    <label for="register-class">Kelas</label>
                    <select id="register-class" name="class_id" required>
                        <option value="">Memuat kelas...</option>
                    </select>
                    <small class="field-error" data-error-for="class_id"></small>
                </div>

                <button class="button button-primary button-full" type="submit" data-submit>
                    <span data-button-label>Daftar</span>
                </button>
            </form>
        </section>
    </div>
</section>
@endsection
