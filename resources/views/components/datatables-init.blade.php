@props(['selector'])
<script>
  $(function () {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ $selector }}');
    }
  });
</script>
