@extends('layout')
@section('content')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-role-modal">
        Add Role
    </button>

    <table class="table table-bordered datatable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Guard</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    @include('administration.role.partials.edit-modal')
    @include('administration.role.partials.create-modal')
@endsection

@push('scripts')
    <script>
        // Datatable definition
        var dt = $('.datatable').DataTable({
            ajax: {
                url: '{!! route('role.index') !!}',
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
                    data: 'guard_name',
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
                                    data-bs-target="#edit-role-modal" data-id=":id">
                                    <i class="bi bi-pencil"></i>
                                </button>`.replace(':id', row.id);
                        const btn_delete =
                            `<form action="" class="d-inline" id="delete-role-form" data-id=":id">
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

        // MODAL EDIT role SHOWN
        $('#edit-role-modal').on('shown.bs.modal', (e) => {
            var id = $(e.relatedTarget).data('id');
            var url = "{{ route('role.edit', ['role' => ':id']) }}".replace(':id', id);
            $('#edit-role-form').attr('data-id', id); //set form's data-id

            $.ajax({
                type: "GET",
                url: url,
                success: function(response) {
                    $('#edit-role-form [name="name"]').val(response.name);

                    var permissions = response.permissions.map(function(item) {
                        return item.name;
                    });
                    $('#edit-permissions').val(permissions).trigger('change');
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

        // DELETE role SUBMITTED
        // HARUS EVENT DELEGATION
        $(document).on('submit', '#delete-role-form', function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var formData = new FormData(this);
            var url = "{{ route('role.destroy', ['role' => ':id']) }}".replace(':id', id);

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
