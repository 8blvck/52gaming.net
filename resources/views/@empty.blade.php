@yield('content')
<?php foreach($_translations as $x => $item):
	$_translations[$item] = __($item); unset($_translations[$x]);
endforeach ?>
<script>
	window.translations = <?= json_encode($_translations, JSON_UNESCAPED_UNICODE); ?>;
</script>
