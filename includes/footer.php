  </main>
</div>
<script>window.CSRF = <?= json_encode(csrf_token()) ?>;</script>
<?php foreach (($scripts ?? []) as $s): ?><script src="<?= e(asset($s)) ?>"></script><?php endforeach; ?>
</body>
</html>
