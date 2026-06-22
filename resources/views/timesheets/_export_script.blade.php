<script>
  $(function() {
    $(document).on('click', '[data-export-table]', function() {
      var tableSelector = $(this).data('export-table');
      var $table = $(tableSelector).first();

      if (!$table.length) {
        return;
      }

      var $exportTable = $table.clone();
      $exportTable.find('[data-export-ignore]').remove();
      $exportTable.find('a, button').each(function() {
        $(this).replaceWith($(this).text());
      });

      var excelHtml = [
        '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">',
        '<head><meta charset="UTF-8"></head>',
        '<body>',
        $('<div>').append($exportTable).html(),
        '</body></html>'
      ].join('');

      var blob = new Blob([excelHtml], {
        type: 'application/vnd.ms-excel'
      });
      var downloadUrl = URL.createObjectURL(blob);
      var downloadLink = document.createElement('a');

      downloadLink.href = downloadUrl;
      downloadLink.download = 'timesheet_record.xls';
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
      URL.revokeObjectURL(downloadUrl);
    });
  });
</script>
