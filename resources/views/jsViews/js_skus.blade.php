<script type="text/javascript">
    let btn_table;

    $(document).ready(function() {
        btn_table = new DataTable('#dt_articulos');
        $("#dt_articulos_length").hide();
        $("#dt_articulos_filter").hide();
    });


    $("#btn_buscar").click(function(){
        BuscarSkus();
    })
    


    function BuscarSkus() {


        var select_mes = $("#id_num_mes option:selected").val();
        var select_anno = $("#id_num_anno option:selected").val();
        var select_vendedor = $("#id_select_vendedor option:selected").val();    
        if (window.sacTableLoading) sacTableLoading('#dt_articulos', true);
        btn_table = new DataTable('#dt_articulos',{
            "ajax": {
                "url": `/api/getHistoryItems/${select_vendedor}/${select_mes}/${select_anno}`,
                "type": "GET",
                "dataSrc": '',
            },
            "responsive": true,
            "destroy": true,
            "order": [
                [2, "desc"]
            ],
            "lengthMenu": [[5,30,50,100,-1], [5,30,50,100,"Todo"]],
            "language": {
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "zeroRecords": "No hay coincidencias",
                "loadingRecords": "Cargando datos...",
                "paginate": {
                    "first":      "Primera",
                    "last":       "Última ",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "lengthMenu": "_MENU_",
                "emptyTable": "No hay SKUs para este vendedor en el mes elegido",
                "search":     ""
            },
            
            'columns': [
                {"title ": "ARTICULO", "data"  : "ARTICULO"},
                {"title ": "DESCRIPCION", "data"  : "DESCRIPCION"},
                {"title ": "META", "data"  : "MetaUND"},
                {"title ": "EJECUTADO", "data"  : "VentaUND"},
                {"title" : "CUMPLIMIENTO %",  "data": "Cumple", render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {"title ": "CLIENTES", "data"  : "CountCliente" , render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            ],
            "columnDefs": [
                {"className": "dt-center", "targets": []},
                {"className": "dt-right", "targets": [2,3,4,5]},
                { "width": "300", "targets": [ 1 ] },
            ],
        });

        $('#txt_search').on( 'keyup', function () {
            var table = $('#dt_articulos').DataTable();
            table.search(this.value).draw();
        });

        
        $( "#select_rows").change(function() {
            var table = $('#dt_articulos').DataTable();
            table.page.len(this.value).draw();
        });

        $("#dt_articulos_length").hide();
        $("#dt_articulos_filter").hide();
        
    }
</script>