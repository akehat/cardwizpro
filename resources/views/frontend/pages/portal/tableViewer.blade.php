@extends('frontend.pages.portal.welcome')

@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.js"></script>
   <style>
      table, th, td {
        border-collapse: collapse;
    }
    th:first-of-type{
        border-radius: 25px 0px 0px 0px;
    }
    th:last-of-type{
        border-radius: 0px 25px 0px 0px;
    }

    th{
        text-align: center!important;
        align-items: center;
    }
    td, th {
        padding: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width:400px!important;
        min-width:400px!important;
        width:400px!important;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        border-radius: 25px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        border: none;
        background-color: #f2f2f2;
    }

    /* Expand on hover */
    td:hover {
        word-wrap: wrap;
        word-break: break-word;
        white-space: normal!important;
        overflow: visible!important;

        /* text-overflow: inherit!important; */

    }
   </style>
<table class="table" id="veiwer-table">
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
</table>

<script>
$(document).ready(function () {
    var Url = window.location.href;
    var columns = [];
    @foreach($columns as $column)
        columns.push({data: '{{ $column }}', name: '{{ $column }}'});
    @endforeach
    var getUrl = (window.location+'').split("?")[0];
    getUrl = getUrl.endsWith("ies")?getUrl.substring(0,getUrl.length - 3) +'ys':getUrl;
    getUrl = getUrl.substring(0,getUrl.length - 1) +"/";
    var dataTable = $('#veiwer-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: Url,
            type: 'GET',
            data: function (d) {
                d.draw = d.draw || 1; // Initialize draw value if not provided
                d.search = {
                    value: $('input[type="search"]').val(), // Assuming the search input type is 'search'
                    regex: false
                };
                d.length = d.length || dataTable.page.len(); // Number of items per page
                d.columns=null;
            }
        },
        columns:  [
            {
                data: 'id',
                name: 'id',
                searchable: true, orderable: true,
                render: function(data, type, row, meta) {
                    // Assuming 'id' is the name of the first column
                    // Generate the link URL dynamically based on the value of the 'id' column
                    return '<a href="'+getUrl+ data + '">' + data + '</a>';
                }
            },
            // Include other columns here
            ...columns.slice(1)
        ], // Use the dynamically generated columns array
        scrollX: true
    }
    );

    // Add event listener for search input changes
    // $('input[type="search"]').on('keyup', function () {
    //     dataTable.ajax.reload(); // Reload DataTable on search input changes
    // });
});
</script>

@endsection
