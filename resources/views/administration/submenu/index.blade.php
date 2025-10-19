@extends('layout')
@section('content')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-submenu-modal">
        Add Submenu
    </button>

    <table class="table table-bordered datatable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Parent</th>
                <th>URL</th>
                <th>Order</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>


    @include('administration.submenu.partials.create-modal')
    @include('administration.submenu.partials.edit-modal')
@endsection

@push('scripts')
    <script>
        // Datatable definition
        var dt = $('.datatable').DataTable({
            ajax: {
                url: '{!! route('submenu.index') !!}',
                dataSrc: ''
            },
            columns: [{
                    data: null,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    },
                    width: 20,
                    render: (data, type, row, meta) => {
                        return meta.row + 1;
                    },
                    orderable: true,
                },
                {
                    data: 'name',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'menu.name',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'url',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'order',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'created_at',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'updated_at',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'id',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    },
                    render: (data, type, row, meta) => {
                        const btn_edit =
                            `<button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#edit-submenu-modal" data-id=":id">
                                    <i class="bi bi-pencil"></i>
                                </button>`.replace(':id', row.id);
                        const btn_delete =
                            `<form action="" class="d-inline" id="delete-submenu-form" data-id=":id">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn icon btn-danger">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>`.replace(':id', row.id);
                        return `${btn_edit} ${btn_delete}`;
                    },
                },
            ]
        });

        // MODAL EDIT SUBMENU SHOWN
        $('#edit-submenu-modal').on('shown.bs.modal', (e) => {
            var id = $(e.relatedTarget).data('id');
            $('#edit-submenu-form').attr('data-id', id); //set form's data-id
            var url = "{{ route('submenu.edit', ['submenu' => ':id']) }}".replace(':id', id);

            $.ajax({
                type: "GET",
                url: url,
                success: function(response) {
                    $(`#edit-menu_id option[value="${response.menu.id}"]`).prop('selected', true);
                    $('#edit-name').val(response.name);
                    $('#edit-url').val(response.url);
                    $('#edit-order').val(response.order);
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        swal.fire("Error", "Unauthorized Acess.", "error");
                    } else {
                        swal.fire("Error", "Unexpected Error.", "error");
                    }
                }
            });
        });

        // DELETE SUBMENU SUBMITTED
        // HARUS EVENT DELEGATION
        $(document).on('submit', '#delete-submenu-form', function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var formData = new FormData(this);
            var url = "{{ route('submenu.destroy', ['submenu' => ':id']) }}".replace(':id', id);

            confirmationModal().then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: (data) => {
                            showToast(data);
                            dt.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            if (xhr.status === 403) {
                                swal.fire("Error", "Unauthorized Acess.", "error");
                            } else {
                                swal.fire("Error", "Unexpected Error.", "error");
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
