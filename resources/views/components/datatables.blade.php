<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  window.initDataTable = function(target) {
    if (!window.jQuery || !$.fn.DataTable) {
      return;
    }

    var $table = target instanceof jQuery ? target : $(target);
    if (!$table.length) {
      return;
    }

    var tableNode = $table.get(0);
    if ($.fn.dataTable.isDataTable(tableNode)) {
      $table.DataTable().destroy();
    }

    $table.DataTable({
      pageLength: 10,
      lengthChange: false,
      pagingType: 'simple',
      ordering: false,
      searching: false,
      destroy: true
    });
  };

</script>
