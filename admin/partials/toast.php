<?php
/**
 * Toast flotante de feedback (éxito o error) para el panel admin.
 *
 * La página padre define $success o $error tras procesar un POST, y luego
 * incluye este partial. Si ambas variables están vacías no se renderiza nada.
 *
 * Comportamiento:
 *   - Aparece con animación (.toast-visible) en el siguiente frame.
 *   - Se autocierra a los 4 s; el usuario también puede cerrarlo con la X.
 *   - Si hay $success, prioridad sobre $error (asumimos que un flujo no
 *     completa con éxito y falla a la vez).
 */
?>
<?php if (!empty($success) || !empty($error)): ?>
<div class="admin-toast <?= !empty($success) ? 'toast-success' : 'toast-error' ?>" id="admin-toast" role="alert">
    <?php if (!empty($success)): ?>
        ✅ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <?php else: ?>
        ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    <?php endif; ?>
    <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Cerrar">✕</button>
</div>
<script>
(function () {
    const toast = document.getElementById('admin-toast');
    if (!toast) return;
    requestAnimationFrame(() => toast.classList.add('toast-visible'));
    setTimeout(() => {
        toast.classList.remove('toast-visible');
        setTimeout(() => toast.remove(), 350);
    }, 4000);
})();
</script>
<?php endif; ?>
