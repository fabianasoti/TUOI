<?php
/**
 * Sección de contacto reutilizable (info + formulario + JS).
 *
 * Variables requeridas antes del include:
 *   $base            → ruta relativa hasta la raíz del proyecto
 *   $c               → array de site_content (para teléfono, email, dirección)
 *   $contact_success → bool   (lo deja includes/contact-handler.php)
 *   $contact_errors  → array  (lo deja includes/contact-handler.php)
 */

$err = function($field) use ($contact_errors) {
    return isset($contact_errors[$field]) ? t($contact_errors[$field]) : '';
};
?>
<section class="ev-contact" id="contacto">
    <div class="ev-contact__inner">

        <div class="ev-contact__info">
            <?php if (empty($contact_compact)): ?>
            <span class="section-label ev-contact__label"><?= t('ev_contact_title') ?></span>
            <h2><?= t('ev_contact_h2') ?></h2>
            <p><?= t('ev_contact_lead') ?></p>
            <?php endif; ?>

            <ul class="ev-contact__list">
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.16h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.75a16 16 0 0 0 8.34 8.34l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[\s\-()]/', '', $c['contact_phone'] ?? '')) ?>">
                        <?= htmlspecialchars($c['contact_phone'] ?? '') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? '') ?>">
                        <?= htmlspecialchars($c['contact_email'] ?? '') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span><?= htmlspecialchars($c['contact_address'] ?? '') ?></span>
                </li>
            </ul>
        </div>

        <div class="ev-contact__form-wrap">

            <div class="ev-form-success" role="status" aria-live="polite" <?= $contact_success ? '' : 'hidden' ?>>
                <p class="ev-form-success__msg"><?= t('ev_contact_ok') ?></p>
                <button type="button" class="ev-form-success__again">
                    <?= t('ev_contact_send_another') ?>
                </button>
            </div>

            <form class="ev-form<?= $contact_success ? ' is-hidden' : '' ?>"
                  method="post" action="#contacto" novalidate
                  data-msg-required="<?= t('ev_form_required') ?>"
                  data-msg-email="<?= t('ev_form_email_bad') ?>"
                  data-msg-consent="<?= t('ev_contact_consent_err') ?>">
                <input type="hidden" name="contact_submit" value="1">
                <!-- Honeypot anti-spam -->
                <div class="ev-form__hp" aria-hidden="true">
                    <label for="c_website">Website</label>
                    <input id="c_website" name="c_website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <div class="ev-form__row ev-form__row--half">
                    <div class="ev-form__group">
                        <label for="c_name"><?= t('ev_contact_name') ?> *</label>
                        <p class="ev-form__error" data-error-for="c_name" <?= $err('c_name') ? '' : 'hidden' ?>><?= $err('c_name') ?></p>
                        <input id="c_name" name="c_name" type="text"
                               placeholder="<?= t('ev_form_ph_name') ?>"
                               value="<?= htmlspecialchars($_POST['c_name'] ?? '') ?>"
                               aria-describedby="err-c_name">
                    </div>
                    <div class="ev-form__group">
                        <label for="c_email"><?= t('ev_contact_email') ?> *</label>
                        <p class="ev-form__error" data-error-for="c_email" <?= $err('c_email') ? '' : 'hidden' ?>><?= $err('c_email') ?></p>
                        <input id="c_email" name="c_email" type="email"
                               placeholder="<?= t('ev_form_ph_email') ?>"
                               value="<?= htmlspecialchars($_POST['c_email'] ?? '') ?>">
                    </div>
                </div>
                <div class="ev-form__group">
                    <label for="c_phone"><?= t('ev_contact_phone') ?></label>
                    <input id="c_phone" name="c_phone" type="tel"
                           placeholder="<?= t('ev_form_ph_phone') ?>"
                           value="<?= htmlspecialchars($_POST['c_phone'] ?? '') ?>">
                </div>
                <div class="ev-form__group">
                    <label for="c_message"><?= t('ev_contact_msg') ?> *</label>
                    <p class="ev-form__error" data-error-for="c_message" <?= $err('c_message') ? '' : 'hidden' ?>><?= $err('c_message') ?></p>
                    <textarea id="c_message" name="c_message" rows="5"
                              placeholder="<?= t('ev_form_ph_msg') ?>"><?= htmlspecialchars($_POST['c_message'] ?? '') ?></textarea>
                </div>
                <p class="ev-form__error" data-error-for="c_consent" <?= $err('c_consent') ? '' : 'hidden' ?>><?= $err('c_consent') ?></p>
                <label class="ev-form__consent<?= $err('c_consent') ? ' has-error' : '' ?>">
                    <input type="checkbox" name="c_consent" value="1"
                           <?= !empty($_POST['c_consent']) ? 'checked' : '' ?>>
                    <span><?= t_raw('ev_contact_consent') ?></span>
                </label>
                <button type="submit" class="btn-primary ev-form__btn">
                    <?= t('ev_contact_send') ?> <span aria-hidden="true">→</span>
                </button>
            </form>
            <script>
            (function () {
                var form    = document.querySelector('.ev-form');
                var success = document.querySelector('.ev-form-success');
                if (!form || !success) return;

                var msgRequired = form.dataset.msgRequired || '';
                var msgEmail    = form.dataset.msgEmail    || '';
                var msgConsent  = form.dataset.msgConsent  || '';

                function showError(field, msg) {
                    var p = form.querySelector('[data-error-for="' + field + '"]');
                    if (!p) return;
                    p.textContent = msg;
                    p.hidden = !msg;
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input) input.classList.toggle('is-invalid', !!msg);
                }
                function clearErrors() {
                    form.querySelectorAll('[data-error-for]').forEach(function (p) {
                        p.textContent = '';
                        p.hidden = true;
                    });
                    form.querySelectorAll('.is-invalid').forEach(function (el) {
                        el.classList.remove('is-invalid');
                    });
                }

                function clientValidate() {
                    clearErrors();
                    var ok = true;
                    var name    = form.elements['c_name'];
                    var email   = form.elements['c_email'];
                    var msg     = form.elements['c_message'];
                    var consent = form.elements['c_consent'];

                    if (!name.value.trim())    { showError('c_name', msgRequired); ok = false; }
                    if (!email.value.trim())   { showError('c_email', msgRequired); ok = false; }
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showError('c_email', msgEmail); ok = false; }
                    if (!msg.value.trim())     { showError('c_message', msgRequired); ok = false; }
                    if (!consent.checked)      { showError('c_consent', msgConsent); ok = false; }
                    return ok;
                }

                form.addEventListener('submit', function (ev) {
                    ev.preventDefault();
                    if (!clientValidate()) {
                        var firstErr = form.querySelector('.ev-form__error:not([hidden])');
                        if (firstErr) firstErr.scrollIntoView({behavior:'smooth', block:'center'});
                        return;
                    }
                    var btn = form.querySelector('button[type="submit"]');
                    if (btn) btn.disabled = true;

                    fetch(form.action.split('#')[0], {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: new FormData(form)
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.ok) {
                            form.reset();
                            form.classList.add('is-hidden');
                            success.hidden = false;
                            setTimeout(function () { success.classList.add('is-visible'); }, 20);
                        } else if (data && data.errors) {
                            Object.keys(data.errors).forEach(function (k) {
                                showError(k, data.errors[k]);
                            });
                            var firstErr = form.querySelector('.ev-form__error:not([hidden])');
                            if (firstErr) firstErr.scrollIntoView({behavior:'smooth', block:'center'});
                        }
                    })
                    .catch(function () {
                        form.submit();
                    })
                    .finally(function () {
                        if (btn) btn.disabled = false;
                    });
                });

                form.querySelectorAll('input, textarea').forEach(function (el) {
                    var clear = function () { showError(el.name, ''); };
                    el.addEventListener('input',  clear);
                    el.addEventListener('change', clear);
                });

                success.querySelector('.ev-form-success__again').addEventListener('click', function () {
                    success.classList.remove('is-visible');
                    setTimeout(function () {
                        success.hidden = true;
                        form.classList.remove('is-hidden');
                        var first = form.querySelector('input[name="c_name"]');
                        if (first) first.focus();
                    }, 250);
                });

                if (!success.hidden) {
                    setTimeout(function () { success.classList.add('is-visible'); }, 20);
                }
            })();
            </script>
        </div>

    </div>
</section>
