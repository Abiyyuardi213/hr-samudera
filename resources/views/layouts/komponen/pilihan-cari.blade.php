@once
@push('gaya')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush
@push('skrip')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
$('.pilihan-cari').each(function () {
    var pilihan = $(this), modal = pilihan.closest('.modal');
    pilihan.select2({theme: 'bootstrap4', width: '100%', dropdownParent: modal.length ? modal : $(document.body)});
});
</script>
@endpush
@endonce
