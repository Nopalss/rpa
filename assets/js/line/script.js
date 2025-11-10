$(document).ready(function () {

    // ---------------------------------
    // FUNGSI ADD/EDIT LINE (PREFERENCE)
    // ---------------------------------
    $('#addLineBtn').on('click', function () {
        Swal.fire({
            title: 'Tambahkan Data Line Baru',
            input: 'text',
            inputLabel: 'Nama Line',
            inputPlaceholder: 'Masukkan nama line...',
            showCancelButton: true,
            confirmButtonText: 'Tambahkan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Nama line tidak boleh kosong!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const lineName = result.value;
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`${HOST_URL}controllers/preference/add_line.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        line_name: lineName
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Oops...', 'Terjadi kesalahan: ' + error.message, 'error');
                    });
            }
        });
    });

    $('#kt_datatable').on('click', '.editLineBtn', function () {
        const lineId = $(this).data('id');
        const currentLineName = $(this).data('name');

        Swal.fire({
            title: 'Edit Data Line',
            input: 'text',
            inputLabel: 'Nama Line',
            inputValue: currentLineName,
            inputPlaceholder: 'Masukkan nama line baru...',
            showCancelButton: true,
            confirmButtonText: 'Simpan Perubahan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Nama line tidak boleh kosong!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const newLineName = result.value;
                if (newLineName === currentLineName) {
                    Swal.fire('Tidak ada perubahan', '', 'info');
                    return;
                }
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`${HOST_URL}controllers/preference/edit_line.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        line_id: lineId,
                        line_name: newLineName
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Oops...', 'Terjadi kesalahan: ' + error.message, 'error');
                    });
            }
        });
    });

})