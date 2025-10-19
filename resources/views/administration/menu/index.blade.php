@extends('layout')
@section('content')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-menu-modal">
        Add Menu
    </button>

    <table class="table table-bordered datatable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Icon</th>
                <th>Order</th>
                <th>Has Child</th>
                <th>URL</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    @include('administration.menu.partials.create-modal')
    @include('administration.menu.partials.edit-modal')
@endsection

@push('scripts')
    <script>
        // Datatable definition
        var dt = $('.datatable').DataTable({
            ajax: {
                url: '{!! route('menu.index') !!}',
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
                    orderable: false,
                },
                {
                    data: 'name',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap');
                    }
                },
                {
                    data: 'icon',
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
                    data: 'has_child',
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
                                    data-bs-target="#edit-menu-modal" data-id=":id">
                                    <i class="bi bi-pencil"></i>
                                </button>`.replace(':id', row.id);
                        const btn_delete =
                            `<form action="" class="d-inline" id="delete-menu-form" data-id=":id">
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

        // MODAL CREATE MENU SHOWN
        $('#create-menu-modal').on('shown.bs.modal', (e) => {
            if ($('html').attr('data-bs-theme') == 'dark') {
                $('#create-icon').trigger('focus'); // due too UI error when dark mode
                $('#create-url').trigger('mouseup'); // due too UI error when dark mode
            }
        });

        // MODAL EDIT MENU SHOWN
        $('#edit-menu-modal').on('shown.bs.modal', (e) => {
            var id = $(e.relatedTarget).data('id');
            var url = "{{ route('menu.edit', ['menu' => ':id']) }}".replace(':id', id);
            $('#edit-menu-form').attr('data-id', id); //set form's data-id

            if ($('html').attr('data-bs-theme') == 'dark') {
                $('#edit-icon').trigger('focus'); // due too UI error when dark mode
                $('#edit-url').trigger('mouseup'); // due too UI error when dark mode
            }

            $.ajax({
                type: "GET",
                url: url,
                success: function(response) {
                    $('#edit-name').val(response.name);
                    $('#edit-order').val(response.order);
                    $('#edit-icon').val(response.icon);
                    $('#edit-has_child').prop('checked', response.has_child);

                    if (response.has_child) {
                        $('#edit-url').attr('disabled', true);
                        $('#edit-url').val('');
                    } else {
                        $('#edit-url').attr('disabled', false);
                        $('#edit-menu-form [id="edit-url"]').val(response.url);
                    }
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

        $('#edit-icon').on('iconpickerShow', function() {
            $('.iconpicker-search').val($('#edit-icon').val())
        });

        $('#create-icon').on('iconpickerShow', function() {
            $('.iconpicker-search').val($('#create-icon').val())
        });

        // DELETE EDIT MENU SUBMITTED
        // HARUS EVENT DELEGATION
        $(document).on('submit', '#delete-menu-form', function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var formData = new FormData(this);
            var url = "{{ route('menu.destroy', ['menu' => ':id']) }}".replace(':id', id);

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
