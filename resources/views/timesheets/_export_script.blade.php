<script>
  $(function() {
    // $(document).on('click', '[data-export-table]', function() {
    //     //
    //   e.preventTDefault();
    //   var tableSelector = $(this).data('export-table');
    //   var $table = $(tableSelector).first();

    //   if (!$table.length) {
    //     return;
    //   }

    //   var $exportTable = $table.clone();
    //   $exportTable.find('[data-export-ignore]').remove();
    //   $exportTable.find('a, button').each(function() {
    //     $(this).replaceWith($(this).text());
    //   });

    //   var excelHtml = [
    //     '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">',
    //     '<head><meta charset="UTF-8"></head>',
    //     '<body>',
    //      $('<div>').append($exportTable).html(),

    //     '</body></html>'
    //   ].join('');

    //   var blob = new Blob([excelHtml], {
    //     type: 'application/vnd.ms-excel'
    //   });
    //   var downloadUrl = URL.createObjectURL(blob);
    //   var downloadLink = document.createElement('a');

    //   downloadLink.href = downloadUrl;
    //   downloadLink.download = 'timesheet_record.xls';
    //   document.body.appendChild(downloadLink);
    //   downloadLink.click();
    //   document.body.removeChild(downloadLink);
    //   URL.revokeObjectURL(downloadUrl);
    // });
    $(document).on('click', '[data-export-table]', function(e) {
    e.preventDefault(); // ခလုတ်ကို နှိပ်လိုက်လျှင် ပုံမှန်လုပ်ဆောင်ချက်ကို ရပ်ပါ

    var tableSelector = $(this).data('export-table');
    var tableInstance = $(tableSelector).DataTable(); // DataTables instance ကို ရယူပါ

    // ၁။ Pagination ကို ပိတ်ပြီး Data အားလုံးကို ဖော်ပြပါ (page.len(-1) = အားလုံး)
    var originalPageLength = tableInstance.page.len(); // မူလ page length ကို မှတ်ထားပါ
    tableInstance.page.len(-1).draw();

    // ၂။ Table ကို Clone လုပ်ပါ
    var $exportTable = $(tableSelector).clone();
    $exportTable.find('[data-export-ignore]').remove();
    $exportTable.find('a, button').each(function() {
        $(this).replaceWith($(this).text());
    });

    // ၃။ Excel ဖိုင်အဖြစ် ပြောင်းပြီး ဒေါင်းလုဒ်လုပ်ပါ
    var excelHtml = [
        '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">',
        '<head><meta charset="UTF-8"></head>',
        '<body>',
        '<table>' + $exportTable.html() + '</table>',
        '</body></html>'
    ].join('');

    var blob = new Blob([excelHtml], { type: 'application/vnd.ms-excel' });
    var downloadUrl = URL.createObjectURL(blob);
    var downloadLink = document.createElement('a');
    downloadLink.href = downloadUrl;
    downloadLink.download = 'timesheet_record.xls';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    URL.revokeObjectURL(downloadUrl);

    // ၄။ Pagination ကို မူလအတိုင်း ပြန်ထားပါ
    tableInstance.page.len(originalPageLength).draw();
});
  });
</script>
