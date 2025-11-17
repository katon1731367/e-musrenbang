<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-MUSRENBANG</title>

    {{-- Zuramai Styles --}}
    <link rel="shortcut icon" href="{{ asset('/assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon"
        href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC"
        type="image/png">

    {{-- bootstrap 5 --}}
    <link rel="stylesheet" href="{{ asset('/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/compiled/css/app-dark.css') }}">

    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>

    {{-- Other Styles --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdn.jsdelivr.net/npm/jquery-loadingModal@1.0.2/css/jquery.loadingModal.min.css"
        rel="stylesheet">

    {{-- other scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-loadingModal@1.0.2/js/jquery.loadingModal.min.js"></script>
    {{-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Load the Latest Bootstrap Icons -->
    <link href="/assets/vendors/JqueryIconpicker/css/bootstrapicons-iconpicker.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/darkmode-select2.css">
    <link rel="stylesheet" href="css/darkmode-iconpicker.css">
    @stack('styles')
</head>

<body>
    <div id="app">
        @include('sidebar')
        <div id="main" class='layout-navbar navbar-fixed'>
            @include('header')
            <div id="main-content">
                <div class="page-heading">
                    <div class="page-title">
                        @php
                            use Illuminate\Support\Facades\Session;
                        @endphp
                        <h2>
                            {{ Session::get('page_title') }}
                        </h2>
                    </div>
                </div>

                @yield('content')

                <footer>
                    <div class="footer clearfix mb-0 text-muted">
                        <div class="float-start">
                            <p>2025 &copy; Kinanda</p>
                        </div>
                        <div class="float-end">
                            <p>Crafted with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                                by <a href="https://saugi.me">Kinanda</a></p>
                        </div>
                    </div>
                </footer>
            </div>

        </div>
    </div>


    <!-- zuramai script -->
    <script src="/assets/static/js/components/dark.js"></script>
    <script src="/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>

    {{-- bootsrap 5 --}}
    <script src="/assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="/assets/vendors/JqueryIconpicker/js/bootstrapicon-iconpicker.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        function showToast(message) {
            Toastify({
                text: message.content,
                duration: 7000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: (message.type == 'success') ? "#61876E" : "#F55050",
            }).showToast();
        }

        function loadingModal(args, text = null) {
            if (args == 'show') {
                $('body').loadingModal({
                    text: text || 'Loading..',
                    animation: 'fadingCircle',
                });
            } else if (args == 'hide') {
                $('body').loadingModal('hide');
                setTimeout(() => $('body').loadingModal('destroy'), 1000);
            }
        }

        $(document).ajaxStop(function() {
            loadingModal('hide');
        });

        $(document).ajaxStart(function() {
            loadingModal('show');
        });

        function confirmationModal() {
            return swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            });
        }

        function displayErrorMessages(errors, formElement, formName) {
            $.each(errors, function(key, value) {
                // add is-invalid class to the corresponsing element 
                var input = formElement.find(`[id="${formName}-${key}"]`);
                input.addClass('is-invalid');

                // Add element contains error message after inputElement
                var errorElement = $(
                    '<div class="invalid-feedback"></div>');
                $.each(value, function(index, message) {
                    errorElement.append(`<div>${message}</div>`);
                });
                input.after(errorElement);
            });
        }

        function removeErrorMessages(formElement) {
            // Clear previous error message
            formElement.find('.invalid-feedback').remove();
            formElement.find('.form-control').removeClass('is-invalid');
            formElement.find('.form-select').removeClass('is-invalid');
        }

        $('.iconpicker').iconpicker({
            // customize the icon picker with the following options
            // THANKS FOR WATCHING!
            title: 'My Icon Picker',
            selected: false,
            defaultValue: true,
            placement: "bottom",
            collision: "none",
            animation: false,
            hideOnSelect: true,
            showFooter: true,
            searchInFooter: false,
            mustAccept: false,
            selectedCustomClass: "bg-primary",
            fullClassFormatter: function(e) {
                return e;
            },
            input: "input,.iconpicker-input",
            inputSearch: false,
            container: false,
            component: ".input-group-addon,.iconpicker-component",
            templates: {
                popover: '<div class="iconpicker-popover popover" role="tooltip"><div class="arrow"></div>' +
                    '<div class="popover-title"></div><div class="popover-content"></div></div>',
                footer: '<div class="popover-footer"></div>',
                buttons: '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button>' +
                    ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">Accept</button>',
                search: '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
                iconpicker: '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
                iconpickerItem: '<a role="button" href="javascript:;" class="iconpicker-item"><i></i></a>'
            }
        });

        $(document).on('click', '.toggle-password', function(e) {
            var password = $(this).siblings('.password');
            // toggle the type attribute
            const type = password.attr("type") === "password" ? "text" : "password";
            password.attr("type", type);
            // toggle the eye icon
            if (type === "text")
                $(this).find('i').removeClass('bi-eye-slash').addClass('bi-eye');
            else
                $(this).find('i').removeClass('bi-eye').addClass('bi-eye-slash');
        });

        function emptyForm(formSelector) {
            // Preserve the CSRF token value
            const csrfToken = formSelector.find('input[name="_token"]').val();

            // Clear other form fields
            formSelector.find('[type="text"]').val('');
            formSelector.find('[type="number"]').val('');
            formSelector.find('[type="checkbox"]').prop('checked', false);
            formSelector.find('option[value=""]').attr('selected', true);

            // Restore the CSRF token
            formSelector.find('input[name="_token"]').val(csrfToken);
        }

        $('.select2').each(function() {
            $(this).select2({
                dropdownParent: $(this).closest('.modal')
            });
        });
    </script>

    {{-- toastify --}}
    @if (session()->has('message'))
        @php
            $message = Session::get('message');
        @endphp
        <script>
            showToast({!! json_encode($message) !!});
        </script>
    @endif

    @stack('scripts')
</body>

</html>
