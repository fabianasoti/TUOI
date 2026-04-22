<?php if (!empty($success) || !empty($error)): ?>
<div class="admin-toast <?= !empty($success) ? 'toast-success' : 'toast-error' ?>" id="admin-toast" role="alert">
    <?php if (!empty($success)): ?>
        ✅ <?= $success ?>
    <?php else: ?>
        ⚠️ <?= htmlspecialchars($error) ?>
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
