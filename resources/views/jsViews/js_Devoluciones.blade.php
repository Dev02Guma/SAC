<script type="text/javascript">
    $("#id_loading").hide();
    tbl_header_inventarios_Fav =  [
        {"title": "FACTURAS","data": "FACTURAS"},
        {"title": "FECHA","data": "FCT_DATE"},
        {"title": "RUTA","data": "FCT_RUTA"},
        {"title": "CLIENTE","data": "FCT_CLIE"},
        {"title": "NOMBRE","data": "FCT_NAME"},
        {"title": "ARTICULO","data": "FCT_ARTI"},
        {"title": "DESCRIPCION","data": "FCT_DESC"},
        {"title": "CANTIDAD","data": "FCT_CANT"},
        {"title": "LOTE","data": "FCT_LOTE"},
    ];

    setTables()
    function setTables(){
        initTable([]);
        
        $("#id_loading").hide();
    }
    


    function initTable(d){
        var rows_table = '';


        if(d.length == 0 ){
            rows_table += `<tr class="border-200">
                            <td colspan="3" class="align-middle text-center">
                                <h6 class="mb-0 text-nowrap">No se encontraron resultados</h6>
                            </td>
                        </tr>`;
        }else{
            $("#tbl_facturas_decolucion tbody").html(rows_table);

            d.forEach(function(item, index) {
                
                rows_table += `<tr class="border-200" ` + item.FCT_BONI + ` >
                                <td class="align-middle">
                                    <h6 class="mb-0 text-nowrap"> ` + item.FCT_DESC + `</h6>
                                    <p class="mb-0">` + item.FCT_ARTI + `</p>
                                </td>
                                <td class="align-middle text-center">` + item.FCT_CANT + `</td>
                                <td class="align-middle text-end">` + item.FCT_LOTE + `</td>
                            </tr>`;
            });
        }
        $("#tbl_facturas_decolucion tbody").html(rows_table);
        


    }

    
    $("#btn_search").click(function(){
        var Factura         = $("#tbl_buscar_tbl_facturas").val();    
            
        Factura             = isValue(Factura,'N/D',true)

        if(Factura === 'N/D' ){
            Swal.fire("Oops", "Digite el Numero de Factura", "error");
        }else{
            $("#id_loading").show();
            $.ajax({
                url: "getDevoluciones",
                type: 'POST',
                data: {
                    FACTURA   : Factura,
                    _token  : "{{ csrf_token() }}" 
                },
                async: true,
                success: function(data) {

                    console.log(data)

                    let fact_client = data[0].FACTURAS
                    let date_client = data[0].FCT_DATE 

                    let name_client = data[0].FCT_NAME
                    let code_client = data[0].FCT_CLIE 
                    let ruta_client = data[0].FCT_RUTA                     

                    
                    $("#lbl_factura").html(fact_client);
                    $("#lbl_fecha_factura").html(date_client);
                    $("#lbl_nombre_cliente").html(code_client + ' - ' + name_client);
                    $("#lbl_ruta").html(ruta_client);

                    initTable(data);
                
                    $("#id_loading").hide();
                },
                error: function(response) {
                    Swal.fire("Oops", "No se ha podido guardar!", "error");
                }
            }).done(function(data) {
                //location.reload();
            });

          
        }

        
    })

</script>
