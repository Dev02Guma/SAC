<script type="text/javascript">
    var Selectors = {
        id_mdl_info_producto: '#modal_info_inventario',
        id_mdl_info_cliente : '#modal_info_cliente',
        MODAL_COMMENT: '#IdmdlComment',
    };
    // Pedidos: por defecto desde el primer día del mes hasta hoy
    const startOfMonth = moment().startOf('month').format('YYYY-MM-DD');
    const endOfMonth   = moment().format('YYYY-MM-DD');
    var labelRange = startOfMonth + " to " + endOfMonth;
    var dta_ventas_mercados ;
    
    dta_ventas_mercados = {
        dataset: {
        VentasDelMes:   [[],[],[]],
        }
    };

   


    function AddPlan() {
        var Codigo = $("#lbl_codigo").text();


        Swal.fire({
            title: '¿Estas Seguro ?',
            text: "¡Esta acción no podrá ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si!',
            target:"",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                $.ajax({
                    url: "AddPlanCrecimiento",
                    type: 'post',
                    data: {
                        id      : Codigo,
                        _token  : "{{ csrf_token() }}" 
                    },
                    async: true,
                    success: function(response) {
                        //Swal.fire("Exito!", "Guardado exitosamente", "success");
                    },
                    error: function(response) {
                        //Swal.fire("Oops", "No se ha podido guardar!", "error");
                    }
                }).done(function(data) {
                   // getComment(id_pedido)
                   location.reload();
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }

    $('#id_range_select').val(labelRange);

    // Se cargan todos los estados; el filtro Pendientes/Procesados/Cancelados es en la tabla
    $("#id_select_status").val(-1);
    
    RangeStat(startOfMonth,endOfMonth)

    
    // ===== Pedidos (nueva UI) ========================================================
    var sacPed = { f: 'pend', ruta: '' }; // al abrir se muestran primero los pendientes
    var SAC_PED_ST = {
        PENDIENTE: { key: 'pend', label: 'Pendiente', cls: 'is-pend' },
        PROCESADO: { key: 'proc', label: 'Procesado', cls: 'is-proc' },
        CANCELADO: { key: 'canc', label: 'Cancelado', cls: 'is-canc' }
    };
    function sacPedSt(row) { return SAC_PED_ST[String(row.ESTADO || '').toUpperCase()] || { key: 'otro', label: row.ESTADO || '—', cls: 'is-canc' }; }
    function sacPedLineas(row) {
        var partes = String(row.ARTICULOS || '').split('],');
        partes = partes.splice(0, partes.length - 1);
        return partes.map(function (v) {
            var d = v.split(';');
            return { cant: String(d[0] || '').replace('[', ''), cod: d[1] || '', nom: d[2] || '', bon: d[3] || '', val: d[4] || '' };
        });
    }
    function sacPedFecha(v) {
        var m = moment(v, 'MMM DD, YYYY hh:mm A', 'en');
        return m.isValid() ? m.date() + ' ' + SAC_MESES[m.month()] + ' · ' + m.format('HH:mm') : sacEsc(v);
    }
    var SAC_ICO_BOX = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 8.7 5 8.7-5"></path></svg>';
    var SAC_ICO_MSG = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>';
    var SAC_ICO_CHEV = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>';

    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl_mst_pedido') return true;
        if (sacPed.f !== 'todos' && sacPedSt(row).key !== sacPed.f) return false;
        if (sacPed.ruta && String(row.RUTA || '') !== sacPed.ruta) return false;
        return true;
    });

    tbl_header_pedido = [
        { "title": "", "data": "id", "render": function (data, type, row) {
            if (type === 'sort' || type === 'type') return parseInt(row.id, 10) || 0;
            if (type !== 'display') return [row.CLIENTE, row.DESCRIPCION, row.DIRECCION, row.RUTA, row.VENDEDOR, row.ESTADO, row.code].join(' ');
            var st = sacPedSt(row);
            var n = parseInt(row.CountsComments, 10) || 0;
            return '<div class="sac-ped ' + st.cls + '">' +
                '<span class="sac-ped-av" aria-hidden="true">' + SAC_ICO_BOX + '<i class="sac-ped-dot"></i></span>' +
                '<div class="sac-ped-main">' +
                  '<div class="sac-ped-top"><span class="sac-ped-cli">' + sacEsc(row.CLIENTE) + sacEsc(row.DESCRIPCION) + '</span>' +
                    '<button type="button" class="sac-chev sac-ped-toggle" aria-expanded="false" aria-label="Ver detalle del pedido">' + SAC_ICO_CHEV + '</button></div>' +
                  '<div class="sac-ped-sub"><span>Detalle: ' + sacEsc(row.DIRECCION) + ' · ' + sacPedFecha(row.FECHA) + '</span>' +
                    '<span class="sac-ped-badge ' + st.cls + '"><i></i>' + st.label + '</span></div>' +
                  '<div class="sac-ped-meta">' +
                    '<strong class="sac-ped-monto sac-num">' + sacMoney(row.MONTO) + '</strong>' +
                    '<button type="button" class="sac-ped-com" data-ped-com aria-label="Ver comentarios (' + n + ')">' + SAC_ICO_MSG + n + '</button>' +
                    '<span class="sac-ped-by">Realizado por ' + sacEsc(row.RUTA) + ' · ' + sacEsc(row.VENDEDOR) + '</span>' +
                  '</div>' +
                '</div>' +
              '</div>';
        }}
    ];

    function sacPedDetalle(row) {
        var lineas = sacPedLineas(row);
        var filas = lineas.map(function (l) {
            var bon = $.trim(l.bon);
            var hayBon = bon && bon !== '0' && bon !== '0.00';
            var val = $.isNumeric(String(l.val).replace(/,/g, '')) ? sacMoney(String(l.val).replace(/,/g, '')) : sacEsc(l.val);
            return '<div class="sac-ped-ln"><span class="sac-ped-ln-art"><span class="sac-art-name-plain">' + sacEsc(l.nom) + '</span><span class="sac-cli-sub d-block">' + sacEsc(l.cod) + '</span></span>' +
                '<span class="text-end sac-num">' + sacEsc(l.cant) + '</span>' +
                '<span class="text-end sac-num ' + (hayBon ? 'sac-ped-bon' : 'sac-muted') + '">' + (hayBon ? sacEsc(bon) : '—') + '</span>' +
                '<strong class="text-end sac-num">' + val + '</strong></div>';
        }).join('');
        var nota = $.trim(row.COMMENT || '');
        var pend = sacPedSt(row).key === 'pend';
        return '<div class="sac-ped-det">' +
            '<div class="sac-ped-ln is-head"><span>Artículo (' + lineas.length + ')</span><span class="text-end">Cantidad</span><span class="text-end">Bonificado</span><span class="text-end">Valor</span></div>' +
            filas +
            '<div class="sac-ped-dfoot">' +
              (nota ? '<div class="sac-ped-nota" role="note"><span class="sac-ped-nota-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path><path d="M8 10h8M8 14h5"></path></svg></span><div><strong>Nota</strong><p>' + sacEsc(nota) + '</p></div></div>' : '<span></span>') +
              (pend ? '<div class="sac-ped-acts"><button type="button" class="btn btn-sm btn-falcon-danger" onclick="ChancesStatus(' + parseInt(row.id, 10) + ',2)">Cancelar</button>' +
                      '<button type="button" class="btn btn-sm btn-primary" onclick="ChancesStatus(' + parseInt(row.id, 10) + ',1)">Procesar</button></div>' : '') +
            '</div></div>';
    }
    function sacPedToggle(tr, abrir) {
        var api = $('#tbl_mst_pedido').DataTable(), row = api.row(tr);
        if (!row.data()) return;
        var open = typeof abrir === 'boolean' ? abrir : !row.child.isShown();
        if (open) { row.child(sacPedDetalle(row.data()), 'sac-ped-child').show(); $(tr).addClass('shown'); }
        else { row.child.hide(); $(tr).removeClass('shown'); }
        $(tr).find('.sac-ped-toggle').attr('aria-expanded', open ? 'true' : 'false');
        sacPedBotonTodo();
    }
    function sacPedBotonTodo() {
        var api = $('#tbl_mst_pedido').DataTable();
        var trs = api.rows({ page: 'current' }).nodes().toArray();
        var todos = trs.length > 0 && trs.every(function (tr) { return api.row(tr).child.isShown(); });
        $('#sacPedTodo').text(todos ? 'Contraer todo' : 'Expandir todo').attr('data-abrir', todos ? '0' : '1');
    }
    // La flecha abre y cierra el detalle
    $('#tbl_mst_pedido').on('click', '.sac-ped-toggle', function (e) {
        e.stopPropagation();
        sacPedToggle($(this).closest('tr')[0]);
    });
    // Clic en la fila: solo abre (si está cerrado). Así se puede seleccionar y copiar sin que se cierre.
    $('#tbl_mst_pedido').on('click', 'tbody > tr:not(.sac-ped-child)', function (e) {
        if ($(e.target).closest('[data-ped-com], a, .btn').length) return;
        if (!$(this).find('.sac-ped').length || $(this).hasClass('shown')) return;
        if (window.getSelection && String(window.getSelection())) return;
        sacPedToggle(this, true);
    });
    $('#tbl_mst_pedido').on('click', '[data-ped-com]', function (e) {
        e.stopPropagation();
        var d = $('#tbl_mst_pedido').DataTable().row($(this).closest('tr')).data();
        if (d) AddComment(encodeURIComponent(JSON.stringify(d)));
    });
    $('#tbl_mst_pedido').on('draw.dt', function () {
        var api = $('#tbl_mst_pedido').DataTable(), info = api.page.info();
        $('#sac_ped_info').html(info.recordsDisplay ?
            'Mostrando <strong>' + (info.start + 1) + '–' + info.end + '</strong> de <strong>' + numeral(info.recordsDisplay).format('0,0') + '</strong> pedidos' :
            'No hay pedidos con este filtro en el rango seleccionado');
        var pag = $('#tbl_mst_pedido_paginate'), foot = $('#sacPedFoot');
        // Al volver a crear la tabla (otro rango de fechas) DataTables genera una paginación nueva;
        // se quita la anterior que ya estaba en el pie para que no queden dos.
        foot.find('.dataTables_paginate').not(pag).remove();
        if (pag.length && !pag.parent().is(foot)) foot.append(pag.addClass('sac-pager'));
        sacPedBotonTodo();
    });
    $(document).on('click', '#sacPedTodo', function () {
        var abrir = $(this).attr('data-abrir') !== '0';
        $('#tbl_mst_pedido').DataTable().rows({ page: 'current' }).nodes().each(function (tr) { sacPedToggle(tr, abrir); });
    });
    $(document).on('click', '[data-ped-f]', function () {
        sacPed.f = $(this).attr('data-ped-f');
        $('[data-ped-f]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        $('#tbl_mst_pedido').DataTable().draw();
    });
    $('#sacPedRuta').on('change', function () { sacPed.ruta = this.value; $('#tbl_mst_pedido').DataTable().draw(); });

    function sacPedCargados(pedidos) {
        var c = { todos: 0, pend: 0, proc: 0, canc: 0 }, rutas = {};
        $.each(pedidos || [], function (i, r) { c.todos++; var k = sacPedSt(r).key; if (c[k] !== undefined) c[k]++; if (r.RUTA) rutas[r.RUTA] = 1; });
        $.each(c, function (k, v) { $('#sac_ped_n_' + k).text(numeral(v).format('0,0')); });
        var sel = $('#sacPedRuta'), actual = sacPed.ruta;
        sel.find('option:not(:first)').remove();
        Object.keys(rutas).sort().forEach(function (r) { sel.append($('<option>').val(r).text(r)); });
        if (actual && rutas[actual]) sel.val(actual); else { sel.val(''); sacPed.ruta = ''; }
    }

    // Actualizar: consulta solo los pendientes del rango/SAC actual y los mezcla en la tabla,
    // conservando filtros, página y pedidos desplegados (sin recargar la página).
    function sacPedActualizar() {
        var btn = $('#sacPedActualizar');
        if (btn.prop('disabled')) return;
        var rango = String($('#id_range_select').val() || '').split('to').map(function (x) { return $.trim(x); });
        if (rango.length < 2 || !rango[0] || !rango[1]) return;
        var api = $('#tbl_mst_pedido').DataTable();
        var abiertos = {};
        api.rows().every(function () { if (this.child.isShown()) abiertos[this.data().id] = true; });
        var antes = {};
        api.rows().data().toArray().forEach(function (r) { antes[r.id] = sacPedSt(r).key; });

        btn.prop('disabled', true).addClass('is-loading');
        $.ajax({
            url: 'getPedidosRangeDates', type: 'post', dataType: 'json',
            data: { DateStart: rango[0], DateEnds: rango[1], Estado: 0, SAC: $('#id_select_sac option:selected').val(), _token: "{{ csrf_token() }}" }
        }).done(function (pendientes) {
            pendientes = pendientes || [];
            var idsNuevos = {}; pendientes.forEach(function (r) { idsNuevos[r.id] = true; });
            // Quita los pendientes que ya tenía la tabla (se reemplazan por la versión fresca)
            api.rows(function (idx, data) { return sacPedSt(data).key === 'pend' || idsNuevos[data.id]; }).remove();
            api.rows.add(pendientes);
            api.draw(false);

            var nuevos = pendientes.filter(function (r) { return !antes[r.id]; }).length;
            var cambiaron = Object.keys(antes).filter(function (id) { return antes[id] === 'pend' && !idsNuevos[id]; }).length;
            sacPedCargados(api.rows().data().toArray());

            // Reabrir los que estaban desplegados y resaltar los nuevos
            api.rows({ page: 'current' }).every(function () {
                var d = this.data(), tr = this.node();
                if (abiertos[d.id]) sacPedToggle(tr, true);
                if (!antes[d.id]) { $(tr).addClass('sac-ped-new'); setTimeout(function () { $(tr).removeClass('sac-ped-new'); }, 2600); }
            });

            var partes = [];
            if (nuevos) partes.push(nuevos + (nuevos === 1 ? ' pendiente nuevo' : ' pendientes nuevos'));
            if (cambiaron) partes.push(cambiaron + (cambiaron === 1 ? ' ya no está pendiente' : ' ya no están pendientes'));
            var msg = partes.length ? partes.join(' · ') : 'Sin cambios en pendientes';
            $('#sacPedActualizarEstado').text(msg);
            btn.attr('title', 'Actualizado ' + moment().format('HH:mm') + ' · ' + msg);
            if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: partes.length ? 'info' : 'success', title: msg, showConfirmButton: false, timer: 2200 });
        }).fail(function () {
            if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'No se pudieron consultar los pendientes', showConfirmButton: false, timer: 2600 });
        }).always(function () { btn.prop('disabled', false).removeClass('is-loading'); });
    }
    $(document).on('click', '#sacPedActualizar', sacPedActualizar);

    // Exportar pedidos (lo filtrado) a CSV
    $(document).on('click', '#sacPedExport', function () {
        var rows = $('#tbl_mst_pedido').DataTable().rows({ search: 'applied' }).data().toArray();
        var q = function (v) { v = v == null ? '' : String(v); return '"' + v.replace(/"/g, '""') + '"'; };
        var csv = [['Pedido', 'Cliente', 'Direccion', 'Fecha', 'Estado', 'Monto', 'Ruta', 'Vendedor', 'Comentarios', 'Nota'].map(q).join(',')];
        rows.forEach(function (r) {
            csv.push([r.code, $.trim((r.CLIENTE || '') + (r.DESCRIPCION || '')), r.DIRECCION, r.FECHA, sacPedSt(r).label, sacNum(r.MONTO), r.RUTA, r.VENDEDOR, r.CountsComments, r.COMMENT].map(q).join(','));
        });
        var blob = new Blob(['﻿' + csv.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'pedidos_' + moment().format('YYYYMMDD_HHmm') + '.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    });

    $('#id_range_select').change(function () {
        Fechas = $(this).val().split("to");
        if(Object.keys(Fechas).length >= 2 ){
            RangeStat(Fechas[0],Fechas[1]);
        } 
    });
    $( "#frm_lab_row").change(function() {
        var table = $('#tbl_mst_pedido').DataTable();
        table.page.len(this.value).draw();
    });

    $("#id_btn_new").click(function(){
        $("#id_loading").show();
        Fechas = $('#id_range_select').val().split("to");
        if(Object.keys(Fechas).length >= 2 ){
            RangeStat(Fechas[0],Fechas[1]);
        } 
    });


    $( "#tbl_select_inventario").change(function() {
        var table = $('#tbl_inventario').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_inventario').on('keyup', function() {        
        var vTablePedido = $('#tbl_inventario').DataTable();
        vTablePedido.search(this.value).draw();
    });


    $( "#tbl_select_cliente").change(function() {
        var table = $('#tbl_mst_clientes').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_cliente').on('keyup', function() {        
        var vTablePedido = $('#tbl_mst_clientes').DataTable();
        vTablePedido.search(this.value).draw();
    });


    $( "#tbl_select_liq6").change(function() {
        var table = $('#tbl_inventario_liq_6').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_liq6').on('keyup', function() {        
        var vTablePedido = $('#tbl_inventario_liq_6').DataTable();
        vTablePedido.search(this.value).draw();
    });

    $( "#tbl_select_liq12").change(function() {
        var table = $('#tbl_inventario_liq_12').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_liq12').on('keyup', function() {        
        var vTablePedido = $('#tbl_inventario_liq_12').DataTable();
        vTablePedido.search(this.value).draw();
    });


    $( "#tbl_select_history_factura").change(function() {
        var table = $('#tbl_historico_factura').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_prod_fact').on('input', function () {
        if ($.fn.dataTable.isDataTable('#tbl_prod_fact')) $('#tbl_prod_fact').DataTable().search(this.value).draw();
    });

    $('#tbl_buscar_history_factura').on('keyup', function() {        
        var vTablePedido = $('#tbl_historico_factura').DataTable();
        vTablePedido.search(this.value).draw();
    });

    $( "#tbl_select_last3m").change(function() {
        var table = $('#tbl_ultm_3m').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_buscar_last3m').on('keyup', function() {        
        var vTablePedido = $('#tbl_ultm_3m').DataTable();
        vTablePedido.search(this.value).draw();
    });


    $( "#tbl_select_nofacturado").change(function() {
        var table = $('#tbl_no_facturado').DataTable();
        table.page.len(this.value).draw();
    });

    $('#tbl_search_nofacturado').on('keyup', function() {        
        var vTablePedido = $('#tbl_no_facturado').DataTable();
        vTablePedido.search(this.value).draw();
    });


    $( "#id_select_status").change(function() {
        var table = $('#tbl_mst_pedido').DataTable();

        var selectedText  = this.selectedOptions[0].text;
        if(selectedText == "Todo"){
            table.search("").draw();
        }else{
            table.search(selectedText).draw();
        }
    });
    function RangeStat(Start,Ends){
        if (window.sacTableLoading) sacTableLoading('#tbl_mst_pedido', true);
        Start       = $.trim(Start)
        Ends        = $.trim(Ends)        
        Estado      = $("#id_select_status option:selected").val();  
        SAC         = $("#id_select_sac option:selected").val();  
        $.ajax({
            url: "getPedidosRangeDates",
            type: 'post',
            dataType: 'json',
            data: {
                DateStart   : Start,
                DateEnds    : Ends,
                Estado      : Estado,
                SAC         : SAC,
                _token  : "{{ csrf_token() }}" 
            },
            async: true,
            success: function(Pedidos) {

                sacPedCargados(Pedidos);
                initTable('#tbl_mst_pedido',Pedidos,tbl_header_pedido,[[0, "desc"]],[]);
                $('#tbl_mst_pedido').DataTable().page.len(10).draw();

                $("#tbl_mst_pedido_length").hide();
                $("#tbl_mst_pedido_filter").hide();

                $("#id_loading").hide();


            },
            error: function () {
                $("#id_loading").hide();
                if (window.sacTableError) sacTableError('#tbl_mst_pedido', 'No se pudieron cargar los pedidos.', function () { RangeStat(Start, Ends); });
            }
        })

    }
    
    $('#id_txt_buscar').on('keyup', function() {        
        var vTablePedido = $('#tbl_mst_pedido').DataTable();
        vTablePedido.search(this.value).draw();
    });
    // ===== Inventario (nueva UI) =====================================================
    var sacInv = { chip: 'todos' };
    var SAC_MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

    function sacEsc(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
    function sacNum(v) { return parseFloat(numeral(v).value()) || 0; }
    function sacInvCant(row) {
        return sacNum(row['total']) + sacNum(row['005']);
    }
    function sacInvReglas(row) {
        if (!row.REGLAS || row.REGLAS == '0') return [];
        return String(row.REGLAS).split(',').map(function (r) { return $.trim(r); }).filter(Boolean);
    }
    function sacIniciales(txt) {
        var w = String(txt || '').replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ0-9 ]/g, ' ').split(/\s+/).filter(Boolean);
        return ((w[0] || '?').charAt(0) + (w[1] || '').charAt(0)).toUpperCase();
    }

    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl_inventario') return true;
        if (sacInv.chip === 'bonif') return sacInvReglas(row).length > 0;
        if (sacInv.chip === 'revision') return row.cuarentena != null;
        return true;
    });

    // Filas sin disponible (0 o menos) se muestran atenuadas, como una promoción desactivada
    $('#tbl_inventario').on('draw.dt', function () {
        var api = $('#tbl_inventario').DataTable();
        api.rows({ page: 'current' }).every(function () {
            var sin = sacInvCant(this.data()) <= 0;
            $(this.node()).toggleClass('sac-inv-off', sin).attr('title', sin ? 'Sin disponible' : null);
        });
    });

    $(document).on('click', '[data-inv-chip]', function () {
        sacInv.chip = $(this).attr('data-inv-chip');
        $('[data-inv-chip]').removeClass('on').attr('aria-pressed', 'false');
        $(this).addClass('on').attr('aria-pressed', 'true');
        $('#tbl_inventario').DataTable().draw();
    });


    function sacOpenArt(row) {
        return 'OpenModal(&#39;' + sacEsc(row.ARTICULO) + '&#39;,&#39;' + sacEsc(row.IMG_URL) + '&#39;)';
    }

    tbl_header_inventarios = [
        { "title": "Artículo", "data": "DESCRIPCION", "render": function (data, type, row) {
            if (type !== 'display') return (row.DESCRIPCION || '') + ' ' + row.ARTICULO + ' ' + (row.LABORATORIO || '');
            var rev = row.cuarentena != null ? ' <span class="badge badge-soft-warning ms-1">En revisión</span>' : '';
            return '<div class="sac-art">' +
                '<span class="sac-thumb">' +
                  (row.IMG_URL ? '<img src="' + sacEsc(row.IMG_URL) + '" alt="" loading="lazy" onerror="this.remove()">' : '') +
                  '<span aria-hidden="true">' + sacEsc(sacIniciales(row.DESCRIPCION)) + '</span>' +
                '</span>' +
                '<span class="sac-art-txt">' +
                  '<span><a href="#!" class="sac-art-name" onclick="' + sacOpenArt(row) + '">' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</a>' + rev + '</span>' +
                  '<span class="sac-art-sub">' + sacEsc(row.ARTICULO) + ' · ' + sacEsc(row.UNIDAD_ALMACEN) + '</span>' +
                '</span></div>';
        }},
        { "title": "Disponible", "data": "total", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            var c = sacInvCant(row);
            return type === 'display' ? '<strong class="sac-num">' + numeral(c).format('0,0') + '</strong>' : c;
        }},
        { "title": "Precio farmacia", "data": "PRECIO_FARMACIA", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            var p = sacNum(row.PRECIO_FARMACIA);
            return type === 'display' ? '<span class="sac-num">C$ ' + numeral(p).format('0,0.00') + '</span>' : p;
        }},
        { "title": "Bonificación", "data": "REGLAS", "defaultContent": "", "render": function (data, type, row) {
            var r = sacInvReglas(row);
            if (type !== 'display') return r.join(' ');
            return r.map(function (x) { return '<span class="sac-tag sac-tag-info">' + sacEsc(x) + '</span>'; }).join(' ');
        }}
    ];

    // ===== Clientes (nueva UI) =======================================================
    var sacCli = { f: 'todos' };
    function sacFecha(v) {
        if (!v) return '—';
        var m = moment(v);
        return m.isValid() ? m.date() + ' ' + SAC_MESES[m.month()] + ' ' + m.year() : '—';
    }
    function sacCliIniciales(nombre) {
        var w = String(nombre || '').toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ0-9 ]/g, ' ').split(/\s+/).filter(Boolean);
        if (w.length > 1 && /^FARM/.test(w[0])) w.shift();
        return ((w[0] || '?').charAt(0) + (w[1] || '').charAt(0));
    }
    function sacCliRuc(row) { return row.RUC || row.NIT || row.CEDULA || ''; }
    function sacMoroso(row) { return row.MOROSO === 'S'; }
    function sacMoney(v) { return 'C$ ' + numeral(sacNum(v)).format('0,0.00'); }
    function sacSaldoCls(v) { v = sacNum(v); return v > 0 ? 'sac-amt-bad' : (v < 0 ? 'sac-amt-warn' : 'sac-amt-zero'); }

    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl_mst_clientes') return true;
        if (sacCli.f === 'moroso') return sacMoroso(row);
        if (sacCli.f === 'saldo') return sacNum(row.SALDO) !== 0;
        return true;
    });
    $(document).on('click', '[data-cli-f]', function () {
        sacCli.f = $(this).attr('data-cli-f');
        $('[data-cli-f]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        $('#tbl_mst_clientes').DataTable().draw();
    });
    $('#tbl_mst_clientes').on('click', 'tbody tr', function (e) {
        if ($(e.target).closest('a[href^="tel:"]').length) return;
        var d = $('#tbl_mst_clientes').DataTable().row(this).data();
        if (d) Modal_Cliente(d.CLIENTE);
    });
    $('#tbl_mst_clientes').on('keydown', 'tbody tr', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); }
    });
    $('#tbl_mst_clientes').on('draw.dt', function () {
        var api = $('#tbl_mst_clientes').DataTable(), info = api.page.info();
        $('#sac_cli_shown').text(numeral(info.end - info.start).format('0,0'));
        $('#sac_cli_total').text(numeral(info.recordsDisplay).format('0,0'));
        $(api.rows({ page: 'current' }).nodes()).attr('tabindex', '0').each(function () {
            var d = api.row(this).data();
            $(this).toggleClass('sac-row-moroso', !!(d && sacMoroso(d)));
        });
    });
    function sacCliContar(rows) {
        var mor = 0, sal = 0;
        $.each(rows || [], function (i, r) { if (sacMoroso(r)) mor++; if (sacNum(r.SALDO) !== 0) sal++; });
        $('#sac_cli_n_todos').text(numeral((rows || []).length).format('0,0'));
        $('#sac_cli_n_moroso').text(numeral(mor).format('0,0'));
        $('#sac_cli_n_saldo').text(numeral(sal).format('0,0'));
    }

    tbl_header_clientes = [
        { "title": "", "data": "CLIENTE", "orderable": false, "searchable": false, "className": "sac-cli-av", "render": function (data, type, row) {
            return '<span class="sac-avatar">' + sacEsc(sacCliIniciales(row.NOMBRE)) +
                '<i class="sac-avatar-dot ' + (sacMoroso(row) ? 'is-bad' : 'is-ok') + '" aria-hidden="true"></i></span>';
        }},
        { "title": "Cliente", "data": "NOMBRE", "render": function (data, type, row) {
            if (type !== 'display') return row.CLIENTE + ' ' + (row.NOMBRE || '') + ' ' + (row.DIRECCION || '') + ' ' + sacCliRuc(row);
            var ruc = sacCliRuc(row);
            return '<div class="sac-cli">' +
                '<span class="sac-cli-name">' + sacEsc(row.CLIENTE) + ' · ' + sacEsc(row.NOMBRE) +
                  (sacMoroso(row) ? ' <span class="sac-tag sac-tag-bad">Moroso</span>' : '') + ' ' + mostrarIcono(row.CLIENTE) + '</span>' +
                (ruc ? '<span class="sac-cli-sub">RUC ' + sacEsc(ruc) + '</span>' : '') +
                '<span class="sac-cli-sub sac-ellipsis" title="' + sacEsc(row.DIRECCION) + '">' + sacEsc(row.DIRECCION) + '</span>' +
                '<span class="sac-cli-sub">Última factura: <strong>' + sacFecha(row.fecha) + '</strong></span>' +
              '</div>';
        }},
        { "title": "Ruta", "data": "VENDEDOR", "render": function (data, type, row) {
            return type === 'display' ? '<span class="sac-tag sac-tag-neutral">' + sacEsc(row.VENDEDOR) + '</span>' : row.VENDEDOR;
        }},
        { "title": "Límite", "data": "LIMITE_CREDITO", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            return type === 'display' ? '<strong class="sac-num">' + sacMoney(row.LIMITE_CREDITO) + '</strong>' : sacNum(row.LIMITE_CREDITO);
        }},
        { "title": "Saldo", "data": "SALDO", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            return type === 'display' ? '<span class="sac-num ' + sacSaldoCls(row.SALDO) + '">' + sacMoney(row.SALDO) + '</span>' : sacNum(row.SALDO);
        }},
        { "title": "Disponible", "data": "CREDITODISP", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            return type === 'display' ? '<span class="sac-num sac-amt-ok">' + sacMoney(row.CREDITODISP) + '</span>' : sacNum(row.CREDITODISP);
        }},
        { "title": "Teléfonos", "data": "TELEFONO1", "orderable": false, "render": function (data, type, row) {
            if (type !== 'display') return (row.TELEFONO1 || '') + ' ' + (row.TELEFONO2 || '');
            var t1 = $.trim(row.TELEFONO1 || ''), t2 = $.trim(row.TELEFONO2 || '');
            return '<span class="sac-tel">' + (t1 ? sacEsc(t1) : '—') + '</span><span class="sac-tel sac-tel-2">' + (t2 ? sacEsc(t2) : '—') + '</span>';
        }},
        { "title": "", "data": "CLIENTE", "orderable": false, "searchable": false, "className": "sac-cli-chev", "render": function () {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>';
        }}
    ];

    // ===== Liquidación (nueva UI) ====================================================
    var SAC_BOX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 8.7 5 8.7-5"></path></svg>';
    function sacLiqDias(row) { var d = parseInt(row.DIAS_VENCIMIENTO, 10); return isNaN(d) ? null : d; }
    function sacLiqFecha(v) {
        if (!v) return '—';
        if (String(v).indexOf('/') > -1) return String(v);
        var m = moment(v);
        return m.isValid() ? m.format('DD/MM/YYYY') : String(v);
    }
    function sacLiqNivel(d) { return d === null ? 'info' : (d < 90 ? 'bad' : (d <= 180 ? 'warn' : 'info')); }

    function sacLiqHeader(maxDias) {
        return [
            { "title": "Artículo", "data": "DESCRIPCION", "render": function (data, type, row) {
                if (type !== 'display') return row.ARTICULO + ' ' + (row.DESCRIPCION || '') + ' ' + (row.LABORATORIO || '') + ' ' + (row.LOTE || '') + ' ' + (row.BODEGA || '');
                var img = row.IMG_URL ? '<img src="' + sacEsc(row.IMG_URL) + '" alt="" loading="lazy" onerror="this.remove()">' : '';
                var sub = [row.LABORATORIO, row.LOTE ? 'Lote ' + row.LOTE : '', row.BODEGA].filter(function (x) { return x; }).map(sacEsc).join(' · ');
                return '<div class="sac-art">' +
                    '<span class="sac-thumb sac-thumb-box">' + img + SAC_BOX + '</span>' +
                    '<span class="sac-art-txt">' +
                      '<span class="sac-liq-name"><span class="sac-liq-code">' + sacEsc(row.ARTICULO) + '</span> · ' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</span>' +
                      '<span class="sac-art-sub">' + sub + '</span>' +
                    '</span></div>';
            }},
            { "title": "Existencia", "data": "totalExistencia", "className": "text-end", "searchable": false, "render": function (data, type, row) {
                var v = sacNum(row.totalExistencia);
                if (type !== 'display') return v;
                return '<strong class="sac-num d-block">' + numeral(v).format('0,0.00') + '</strong><span class="sac-unit">' + sacEsc(row.UNIDAD_VENTA) + '</span>';
            }},
            { "title": "Vencimiento", "data": "fecha_vencimientoR", "searchable": false, "render": function (data, type, row) {
                if (type !== 'display') { var d = sacLiqDias(row); return d === null ? 99999 : d; }
                return '<span class="sac-num sac-liq-date">' + sacEsc(sacLiqFecha(row.fecha_vencimientoR)) + '</span>';
            }},
            { "title": "Días restantes", "data": "DIAS_VENCIMIENTO", "searchable": false, "render": function (data, type, row) {
                var d = sacLiqDias(row);
                if (type !== 'display') return d === null ? 99999 : d;
                if (d === null) return '<span class="sac-muted">—</span>';
                var n = sacLiqNivel(d), w = Math.max(4, Math.min(100, d / maxDias * 100));
                return '<div class="sac-dias"><span class="sac-tag sac-tag-' + n + '">' + numeral(d).format('0,0') + (d === 1 ? ' día' : ' días') + '</span>' +
                    '<span class="sac-dias-bar"><span class="is-' + n + '" style="width:' + w.toFixed(1) + '%"></span></span></div>';
            }}
        ];
    }
    tbl_header_inventarios_liq = sacLiqHeader(365);
    var tbl_header_inventarios_liq6 = sacLiqHeader(180);

    // Pie: "Mostrando 1–5 de 14 lotes" + paginación en la misma fila
    $('#tbl_inventario_liq_12, #tbl_inventario_liq_6').on('draw.dt', function () {
        var id = this.id, api = $(this).DataTable(), info = api.page.info();
        var foot = $('[data-liq-foot="' + id + '"]');
        foot.find('.sac-liq-info').html(info.recordsDisplay ?
            'Mostrando <strong>' + (info.start + 1) + '–' + info.end + '</strong> de <strong>' + numeral(info.recordsDisplay).format('0,0') + '</strong> lotes' :
            'Sin lotes que coincidan');
        var pag = $('#' + id + '_paginate');
        // Al volver a crear la tabla (otro rango de fechas) DataTables genera una paginación nueva;
        // se quita la anterior que ya estaba en el pie para que no queden dos.
        foot.find('.dataTables_paginate').not(pag).remove();
        if (pag.length && !pag.parent().is(foot)) foot.append(pag.addClass('sac-pager'));
    });

    // Exportar a CSV (lo que se ve filtrado en la ventana activa)
    $('#sacLiqExport').on('click', function () {
        var id = $('#crm-deals').hasClass('active') ? 'tbl_inventario_liq_6' : 'tbl_inventario_liq_12';
        var rows = $('#' + id).DataTable().rows({ search: 'applied', order: 'applied' }).data().toArray();
        var q = function (v) { v = v == null ? '' : String(v); return '"' + v.replace(/"/g, '""') + '"'; };
        var csv = [['Articulo', 'Descripcion', 'Laboratorio', 'Lote', 'Bodega', 'Existencia', 'Unidad', 'Vencimiento', 'Dias restantes'].map(q).join(',')];
        rows.forEach(function (r) {
            csv.push([r.ARTICULO, r.DESCRIPCION, r.LABORATORIO, r.LOTE, r.BODEGA, sacNum(r.totalExistencia), r.UNIDAD_VENTA, sacLiqFecha(r.fecha_vencimientoR), r.DIAS_VENCIMIENTO].map(q).join(','));
        });
        var blob = new Blob(['﻿' + csv.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'liquidacion_' + (id === 'tbl_inventario_liq_6' ? '6' : '12') + 'meses_' + moment().format('YYYYMMDD') + '.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    });


        
                $('#tbl_mst_pedido').on('click', 'td.detalles-Pedido', function () {
                    var table = $('#tbl_mst_pedido').DataTable();

                    var tr = $(this).closest('tr');
                    var row = table.row(tr);
                    var data = table.row( $(this).parents('tr') ).data();


                    if ( row.child.isShown() ) {
                        $("#dv-"+data.id).hide();
                        row.child.hide();
                        tr.removeClass('shown');
                    }
                    else {
                        $("#dv-"+data.id).show();
                        show_detalles_pedido(row.child,data);
                        tr.addClass('shown');
                    }

                });

                $('#tbl_historico_factura').on('click', 'td.detalles-factura', function () {
                    var table = $('#tbl_historico_factura').DataTable();

                    var tr = $(this).closest('tr');
                    var row = table.row(tr);
                    var data = table.row( $(this).parents('tr') ).data();
                    
                    if ( row.child.isShown() ) {
                        $("#dv-"+data.FACTURA).hide();
                        row.child.hide();
                        tr.removeClass('shown');
                        tr.find('.sac-chev').attr('aria-expanded', 'false');
                    }
                    else {
                        $("#dv-"+data.FACTURA).show();
                        show_detalles_factura(row.child,data);
                        tr.addClass('shown');
                        tr.find('.sac-chev').attr('aria-expanded', 'true');
                    }

                });
                function AddComment(row){
                    obj = JSON.parse(decodeURIComponent(row))

                    var fecha_humana = obj.FECHA

                    $("#id_modal_name_item").text(obj.CLIENTE + obj.DESCRIPCION)
                    $("#id_modal_articulo").text(obj.DIRECCION)
                    $("#id_modal_nSoli").text(obj.code)
                    $("#id_modal_Fecha").text(fecha_humana)


                    $("#id_modal_vendedor").text(obj.VENDEDOR)
                    $("#id_comentario_pedido").text(obj.COMMENT)
                    
                    
                    var addcomment_ = document.querySelector(Selectors.MODAL_COMMENT);
                    var mdl_comment = new window.bootstrap.Modal(addcomment_);
                
                    mdl_comment.show();
                    getComment(obj.code)

                }


            function show_detalles_pedido(callback,detalles_pedido) {
                var tbody = '';

                var lineas_pedido = detalles_pedido.ARTICULOS.split('],')   
                
                lineas_pedido = lineas_pedido.splice(0,lineas_pedido.length-1) ;
                $.each( lineas_pedido, function( key, value ) {
                    var Lineas_detalles     = value.split(';') 
                    tbody += `<tr>
                                <td class="align-middle">
                                    <h6 class="mb-0 text-nowrap">` +Lineas_detalles[2] + `</h6>
                                    <p class="mb-0">` +Lineas_detalles[1] + `</p>
                                </td>
                                <td class="align-middle text-center">` +Lineas_detalles[0].replace("[","") + `</td>
                                <td class="align-middle text-center">` +Lineas_detalles[3] + `</td>
                                <td class="align-middle text-end">` +Lineas_detalles[4] + `</td>
                            </tr>`
                });

                var template =`
                    <div class="card">
                        <div class="card-body">                            
                            <div class="table-responsive scrollbar mt-4 fs--1">
                                <table class="table table-striped border-bottom">
                                    <thead class="light">
                                        <tr class="bg-primary text-white dark__bg-1000">
                                        <th class="border-0">ARTICULO (  ` + lineas_pedido.length +`  ) </th>
                                        <th class="border-0 text-center">CANTIDAD</th>
                                        <th class="border-0 text-center">BONIFICADO</th>
                                        <th class="border-0 text-end">VALOR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ` + tbody + `                                    
                                    </tbody>
                                </table>
                            </div>                            
                        </div>
                        <div class="card-footer bg-light">
                            <p class="fs--1 mb-0"><strong>Nota: </strong> ` + detalles_pedido.COMMENT +`</p>
                        </div>
                    </div>`

                callback($(template)).show();
            }

            function show_detalles_factura(callback,detalles_pedido) {
                $.get( "getDetallesFactura/" + detalles_pedido.FACTURA, function( data ) {

                    var tbody = '';

                    $.each( data, function( key, value ) {

                        tbody += '<tr><td><span class="sac-art-name-plain">' + sacEsc(value.DESCRIPCION) + '</span><span class="sac-cli-sub d-block">' + sacEsc(value.ARTICULO) + '</span></td>' +
                            '<td class="text-end sac-num">' + numeral(value.CANTIDAD).format('0,0') + '</td>' +
                            '<td class="text-end sac-num sac-link-soft">' + sacMoney(value.PRECIO_UNITARIO) + '</td>' +
                            '<td class="text-end sac-num"><strong>' + sacMoney(value.PRECIO_TOTAL) + '</strong></td></tr>';
                        
                        });

                        var template = '<div class="sac-fdet"><table class="sac-fdet-tbl"><thead><tr>' +
                            '<th>Artículo</th><th class="text-end">Cantidad</th><th class="text-end">Bonificado</th><th class="text-end">Valor</th>' +
                            '</tr></thead><tbody>' + tbody + '</tbody></table></div>';

                        callback($(template)).show();

                })
                
            }


            function getComment(Id){
                var items_comment = '';
                $("#id_textarea_comment").val(items_comment)
                $.ajax({
                    url: 'getCommentPedido',
                    type: 'post',
                    data: {
                        id_item     : Id,                  
                        _token      : "{{ csrf_token() }}" 
                    }, 
                    async: false,
                    dataType: "json",
                    success: function(data){
                        $.each(data,function(key, c) {
                            var var_borrar = ''

                            var_borrar = '<a href="#!" onClick="DeleteComment('+c.id_coment+' , '+ "'" +Id + "'" +' )">Borrar</a> &bull; '                             

                            var date_comment = moment(c.date_coment).format("D MMM, YYYY")
                            items_comment += ' <div class="d-flex mt-3">'+
                                                    '<div class="avatar avatar-xl">'+
                                                        '<img class="rounded-circle" src="{{ asset("images/user/avatar-4.jpg") }}" alt="" />'+
                                                    '</div>'+
                                                    '<div class="flex-1 ms-2 fs--1">'+
                                                        '<p class="mb-1 bg-200 rounded-3 p-2">'+
                                                        '<a class="fw-semi-bold" href="!#">'+c.player_id.toUpperCase()+'</a> '+
                                                        ' '+c.orden_comment+'  </p>'+
                                                        '<div class="px-2">'+
                                                        var_borrar+
                                                        date_comment+'</div>'+
                                                    '</div>'+
                                                '</div>'
                        }); 	 
                    },
                    error: function(data) {
                        //alert('error');
                    }
                }); 

                $("#id_comment_item").html(items_comment)
            }
    function DeleteComment(id_comment,id_pedido){
        Swal.fire({
            title: '¿Estas Seguro de borrar el Comentario?',
            text: "¡Esta acción no podrá ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si!',
            target:"",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                $.ajax({
                    url: "DeleteCommentPedido",
                    type: 'post',
                    data: {
                        id      : id_comment,
                        _token  : "{{ csrf_token() }}" 
                    },
                    async: true,
                    success: function(response) {
                        //Swal.fire("Exito!", "Guardado exitosamente", "success");
                    },
                    error: function(response) {
                        //Swal.fire("Oops", "No se ha podido guardar!", "error");
                    }
                }).done(function(data) {
                    getComment(id_pedido)
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }           
    $('#id_textarea_comment').keydown(function(event){
        if (event.which == 13){

            var id_Item = $("#id_modal_nSoli").text()
            var value = $(this).val();

            $.ajax({
                url: "AddCommentPedido",
                type: 'post',
                data: {
                    id_item     : id_Item,
                    comment     : value,                    
                    _token      : "{{ csrf_token() }}" 
                },
                async: true,
                success: function(response) {
                    getComment(id_Item)
                },
                error: function(response) {
                   // Swal.fire("Oops", "No se ha podido guardar!", "error");
                }
            }).done(function(data) {
                //location.reload();
            });
        }
    });             
    function ChancesStatus(id_producto,Valor){

        // Valor 1 = procesar, 2 = cancelar. Tras guardar se actualiza solo esa fila (sin recargar),
        // conservando filtros, página y pedidos desplegados.
        var procesar = parseInt(Valor, 10) === 1;
        var toast = function (icon, title) {
            Swal.fire({ toast: true, position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: icon === 'error' ? 2600 : 2200 });
        };

        Swal.fire({
            title: procesar ? '¿Procesar este pedido?' : '¿Cancelar este pedido?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: procesar ? '#2B3B9E' : '#C42B3A',
            cancelButtonColor: '#64748B',
            confirmButtonText: procesar ? 'Sí, procesar' : 'Sí, cancelar',
            cancelButtonText: 'Volver',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve) {
                    $.ajax({
                        url: "ChancesStatus",
                        type: 'post',
                        dataType: 'json',
                        data: {
                            id      : id_producto,
                            Valor   : Valor,
                            _token  : "{{ csrf_token() }}"
                        }
                    }).done(function (response) {
                        resolve({ ok: !!(response && response.original) });
                    }).fail(function () {
                        resolve({ ok: false });
                    });
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            if (!result.value || !result.value.ok) {
                toast('error', procesar ? 'No se pudo procesar el pedido' : 'No se pudo cancelar el pedido');
                return;
            }
            if ($.fn.dataTable.isDataTable('#tbl_mst_pedido')) {
                var api = $('#tbl_mst_pedido').DataTable();
                var row = api.row(function (idx, data) { return String(data.id) === String(id_producto); });
                var d = row.data();
                if (d) {
                    var abierto = row.child.isShown();
                    d.ESTADO = procesar ? 'PROCESADO' : 'CANCELADO';
                    d.COLOR  = procesar ? 'success' : 'info';
                    row.data(d).invalidate();
                    api.draw(false);
                    // Si seguía desplegado (y visible con el filtro activo) se vuelve a pintar su detalle sin botones
                    if (abierto) {
                        var tr = row.node();
                        if (tr && document.body.contains(tr)) sacPedToggle(tr, true);
                        else { row.child.hide(); $(tr).removeClass('shown').find('.sac-ped-toggle').attr('aria-expanded', 'false'); }
                    }
                    sacPedCargados(api.rows().data().toArray());
                }
            }
            toast('success', procesar ? 'Pedido procesado' : 'Pedido cancelado');
        });
    }

    var cod_client = [];

    $.get( "getPlanCrecimientoIco", function( data ) {
        $('.activo').removeClass('text-warning');
                cod_client = data
    })

    function mostrarIcono(id){
        
        for(var i = 0; i < cod_client.length; i++){
            if(cod_client[i]['CODIGO_CLIENTE'] == id){
                return '<span class="text-warning fas fa-crown me-1"></span>';                
            }            
        }
        return "";        
    }

    $.ajax({
        url: "dtaEstadisticas",       
        type: 'get',
        async: true,
        success: function(data) {
            Estadisticas(data[0].Estadistica)
        },
        error: function(response) {
            //swal("Oops", "No se ha podido guardar!", "error");
        }
    }).done(function(data) {
        //mostrarRequisado(num_orden, id_articulo, 2);
    });

    // Carga inicial de Inventario, Liquidación (12 y 6 meses) y Clientes; se puede reintentar si falla
    var SAC_GETDATA_TBLS = ['#tbl_inventario', '#tbl_inventario_liq_12', '#tbl_inventario_liq_6', '#tbl_mst_clientes'];
    function sacCargarDatos() {
    SAC_GETDATA_TBLS.forEach(function (sel) { if (window.sacTableLoading) sacTableLoading(sel, true); });
    $.ajax({
        url: "getData",
        type: 'get',
        async: true,
        success: function(data) {
            var Users = ''
            initTable('#tbl_inventario',data[0].Inventario,tbl_header_inventarios,[[0, "asc"]],[]);
            $('#tbl_inventario').DataTable().page.len(parseInt($('#tbl_select_inventario').val(), 10) || 10).draw();
            $('#sac_liq_n12').text(numeral((data[0].Liq12Meses || []).length).format('0,0'));
            $('#sac_liq_n6').text(numeral((data[0].Liq6Meses || []).length).format('0,0'));
            initTable('#tbl_inventario_liq_12',data[0].Liq12Meses,tbl_header_inventarios_liq,[[3, "asc"]],[]);
            initTable('#tbl_inventario_liq_6',data[0].Liq6Meses,tbl_header_inventarios_liq6,[[3, "asc"]],[]);
            sacCliContar(data[0].Clientes);
            initTable('#tbl_mst_clientes',data[0].Clientes,tbl_header_clientes,[[1, "asc"]],[]);
            $('#tbl_mst_clientes').DataTable().page.len(parseInt($('#tbl_select_cliente').val(), 10) || 25).draw();
        
            $("#tbl_mst_clientes_length").hide();
            $("#tbl_mst_clientes_filter").hide();

            $("#tbl_inventario_length").hide();
            $("#tbl_inventario_filter").hide();

            $("#tbl_inventario_liq_6_length").hide();
            $("#tbl_inventario_liq_6_filter").hide();

            $("#tbl_inventario_liq_12_length").hide();
            $("#tbl_inventario_liq_12_filter").hide();

            TBLCL = $("#tbl_mst_clientes").DataTable();

            // Los morosos se marcan en el evento draw (clase sac-row-moroso)

            $("#id_loading").hide();
        },
        error: function(response) {
            $("#id_loading").hide();
            var msgs = {
                '#tbl_inventario': 'No se pudo cargar el inventario.',
                '#tbl_inventario_liq_12': 'No se pudo cargar la liquidación a 12 meses.',
                '#tbl_inventario_liq_6': 'No se pudo cargar la liquidación a 6 meses.',
                '#tbl_mst_clientes': 'No se pudieron cargar los clientes.'
            };
            if (window.sacTableError) SAC_GETDATA_TBLS.forEach(function (sel) { sacTableError(sel, msgs[sel], sacCargarDatos); });
        }
    });
    }
    sacCargarDatos();


    function Estadisticas(Data){
        
        $("#id_Venta_Meta").text('C$ ' + numeral(Data['Venta_Meta']).format('0,00.00'))
        $("#id_Venta_Real").text('C$ ' + numeral(Data['Venta_Real']).format('0,00.00'))
        $("#id_Venta_Actu").text('C$ ' + numeral(Data['Venta_Actu']).format('0,00.00'))
        $("#id_Venta_Week").text('C$ ' + numeral(Data['Venta_Week']).format('0,00.00'))
        $("#id_Venta_Week_Label").text(Data['Venta_Week_Label'])

        $("#id_Venta_Porc").text(numeral(Data['Venta_Porc']).format('0,0') + ' %')
        $("#sacBarVenta").css('width', Math.max(0, Math.min(100, numeral(Data['Venta_Porc']).value() || 0)) + '%')

        $("#id_Dias_Habiles").text(numeral(Data['Dias_Habiles']).format('0,00'))
        $("#id_Dias_Facturados").text(numeral(Data['Dias_Facturados']).format('0,00'))
        $("#id_Dias_porcent").text(Data['Dias_porcent'])

        $("#id_Cliente_Meta").text(Data['Cliente_Meta'])
        $("#id_Cliente_Real").text(Data['Cliente_Real'])
        $("#id_Cliente_Porc").text(Data['Cliente_Porc'] + ' %')
        $("#sacBarClientes").css('width', Math.max(0, Math.min(100, numeral(Data['Cliente_Porc']).value() || 0)) + '%')

    }


    function initTable(id,datos,Header,Order,Show){
        $(id).DataTable({
            "data": datos,
            "destroy": true,
            "info": false,
            "bPaginate": true,
            "order": Order,
            "lengthMenu": [
                [5, -1],
                [5, "Todo"]
            ],
            "language": {
                "zeroRecords": "No hay resultados que coincidan",
                "paginate": {
                    "first": "Primera",
                    "last": "Última ",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "lengthMenu": "MOSTRAR _MENU_",
                "emptyTable": "-",
                "search": "BUSCAR"
            },
            'columns': Header,
            "columnDefs": [
                {
                    "visible": false,
                    "searchable": false,
                    "targets": Show
                },
            ],
        });
    }
    function initTable_modal(id,datos,Header){
        $(id).DataTable({
            "data": datos,
            "destroy": true,
            "info": false,
            "bPaginate": false,
            "searching": false,
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [5, -1],
                [5, "Todo"]
            ],
            "language": {
                "zeroRecords": "No hay resultados que coincidan",
                "paginate": {
                    "first": "Primera",
                    "last": "Última ",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "lengthMenu": "MOSTRAR _MENU_",
                "emptyTable": "-",
                "search": "BUSCAR"
            },
            'columns': Header,
        });
    }
    


    $( "#select_ruta").change(function() {
        vTableClientes.search(this.value).draw();
    });
    $('#tbl_bodegas').on('click', 'td.detalles-Lotes', function () {
        var table = $('#tbl_bodegas').DataTable();
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var attx = $("#id_codigo_articulo").text();

        var data = table.row( $(this).parents('tr') ).data();

        if ( row.child.isShown() ) {
            $("#dv-"+data.BODEGA).hide();
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            $("#dv-"+data.BODEGA).show();
            format(row.child,data.BODEGA,attx);
            tr.addClass('shown');
        }

    });

    function format(callback,bodega,art) {
        
        $.ajax({
            url:'getLotes/'+bodega+"/"+art,
            dataType: "json",
            complete: function (response) {
                var data = JSON.parse(response.responseText);
                var ia =0;
                var thead = '',  tbody = '';

                for (var key in data) {
                    thead += '<th class="text-nowrap">LOTE</th>';
                    thead += '<th class="text-nowrap text-end">CANT. DISPONIBLE</th>';
                   // thead += '<th class="text-nowrap text-end">CANT. INGRESADA POR COMPRA</th>';
                   // thead += '<th class="text-nowrap text-end">FECHA ULTM. INGRESO COMPRA</th>';
                   // thead += '<th class="text-nowrap text-end">FECHA DE CREACION</th>';
                    thead += '<th class="text-nowrap text-end">FECHA VENCIMIENTO</th>';
                }

                $.each(data, function (i, d) {

                    $.each(d, function (a, b) {
                        ia++;
                    });


                    for (var x=0; x<ia; x++) {

                        var ART = "'" + d[x]["ARTICULO"] + "'";
                        var lt = "'" + d[x]["LOTE"] + "'";

                        tbody += '<tr class="center">' +
                            '<td class="text-truncate">' + d[x]["LOTE"] + '</td>'+
                            '<td class="text-truncate text-end">' + d[x]["CANT_DISPONIBLE"] + '</td>'+
                          //  '<td class="text-truncate text-end">' + d[x]["CANTIDAD_INGRESADA"] + '</td>'+
                          //  '<td class="text-truncate text-center">' + d[x]["FECHA_INGRESO"] + '</td>'+
                          //  '<td class="text-truncate text-center">' + d[x]["FECHA_ENTRADA"] + '</td>'+
                            '<td class="text-truncate text-center">' + d[x]["FECHA_VENCIMIENTO"] + '</td>'+
                            '</tr>';
                    }
                });

                if (ia==0){
                    thead += '<th></th>';
                    tbody += '<tr><td>BODEGA SIN EXISTENCIA</td></tr>';
                }
                callback($('<table class="table fs--1 mb-0 overflow-hidden"> <thead class="bg-200 text-900">' + thead + '</thead>' + tbody + '</table>')).show();
            },
            error: function () {
                Swal.fire(
                    'Oops...',
                    'Hubo un error al cargar los detalles!',
                    'error'
                )
            }
        });
    }
    tbl_header_bodega =  [
               
                {"title": "","data": "CANT_DISPONIBLE",  "className":'detalles-Lotes',"render": function(data, type, row, meta) {
                    return `<div class="border border-1 border-300 rounded-2 p-3 ask-analytics-item position-relative">
                      <div class="d-flex align-items-center mb-3"><span class="far fa-arrow-alt-circle-down text-success"></span><a class="stretched-link text-decoration-none" href="#!">
                          <h5 class="fs--1 text-600 mb-0 ps-3"> `+ row.BODEGA + " | " + row.NOMBRE + `</h5>
                        </a></div>
                      <h5 class="fs-0 text-900 mb-0 me-2">Cantidad Disponible <span class="badge rounded-pill ms-3 badge-soft-info ">`+ numeral(row.CANT_DISPONIBLE).format('0,00.00')  +`</span></h5>
                    </div>`
                }},
                ]
    tbl_header_nivel_precio =  [
                {"title": "BODEGA","data": "BODEGA", "render": function(data, type, row, meta) {
                    return `<span class="badge rounded-pill ms-3 badge-soft-success ">`+ row.NIVEL_PRECIO +`</span> `
                }},
                {"title": "DISPONIBLE","data": "DISPONIBLE", "render": function(data, type, row, meta) {
                    
                    return `<span class="badge rounded-pill ms-3 badge-soft-info ">`+ numeral(row.PRECIO).format('0,00.00')  +`</span> `
                }},
                ]
    // ===== Modal de artículo (nueva UI) =====
    var SAC_ICO_IMG = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>';
    function sacPrecio(lista, i) {
        var p = lista && lista[i] ? lista[i].PRECIO : null;
        return p == null ? 'C$ —' : sacMoney(p);
    }
    function sacVenceTag(f) {
        // f = "dd/mm/aaaa"
        var p = String(f || '').split('/');
        if (p.length !== 3) return '<span class="sac-tag sac-tag-neutral">' + sacEsc(f || '—') + '</span>';
        var d = new Date(+p[2], +p[1] - 1, +p[0]), dias = Math.round((d - new Date()) / 864e5);
        var cls = dias < 90 ? 'sac-tag-bad' : (dias <= 180 ? 'sac-tag-warn' : 'sac-tag-neutral');
        return '<span class="sac-tag ' + cls + ' sac-num">' + sacEsc(f) + '</span>';
    }
    function sacBodegaHtml(b, i) {
        var cant = sacNum(b.CANT_DISPONIBLE);
        return '<div class="sac-bod" data-bod="' + sacEsc(b.BODEGA) + '">' +
            '<button type="button" class="sac-bod-head" aria-expanded="false" aria-controls="sacBodLotes' + i + '">' +
              '<span class="sac-chev" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg></span>' +
              '<span class="sac-bod-txt"><span class="sac-bod-name">' + sacEsc(b.BODEGA) + ' · ' + sacEsc(b.NOMBRE) + '</span><span class="sac-bod-sub">Cantidad disponible</span></span>' +
              '<span class="sac-tag ' + (cant > 0 ? 'sac-tag-info' : 'sac-tag-neutral') + ' sac-num">' + numeral(cant).format('0,0.00') + '</span>' +
            '</button>' +
            '<div class="sac-bod-lotes" id="sacBodLotes' + i + '" hidden></div>' +
          '</div>';
    }
    // Desplegar una bodega: consulta sus lotes la primera vez
    $(document).on('click', '#sacInvBodegas .sac-bod-head', function () {
        var head = $(this), bod = head.closest('.sac-bod'), box = bod.find('.sac-bod-lotes');
        var abrir = head.attr('aria-expanded') !== 'true';
        head.attr('aria-expanded', abrir ? 'true' : 'false');
        bod.toggleClass('is-open', abrir);
        box.prop('hidden', !abrir);
        if (!abrir || box.data('cargado')) return;
        box.html('<p class="sac-bod-msg">Cargando lotes…</p>');
        $.ajax({ url: 'getLotes/' + bod.attr('data-bod') + '/' + $('#id_codigo_articulo').text(), dataType: 'json' })
            .done(function (r) {
                var lotes = (r && r.data) ? r.data : [];
                box.data('cargado', true);
                if (!lotes.length) { box.html('<p class="sac-bod-msg">Bodega sin existencia en lotes</p>'); return; }
                box.html('<div class="sac-lote is-head"><span>Lote</span><span class="text-end">Cant. disponible</span><span class="text-end">Fecha vencimiento</span></div>' +
                    lotes.map(function (l) {
                        return '<div class="sac-lote"><span class="sac-num"><strong>' + sacEsc(l.LOTE) + '</strong></span>' +
                            '<span class="text-end sac-num">' + sacEsc(l.CANT_DISPONIBLE) + '</span>' +
                            '<span class="text-end">' + sacVenceTag(l.FECHA_VENCIMIENTO) + '</span></div>';
                    }).join(''));
            })
            .fail(function () { box.html('<p class="sac-bod-msg sac-err">No se pudieron cargar los lotes</p>'); });
    });

    function OpenModal(Id, img){
        var req = window.sacArtReq = (window.sacArtReq || 0) + 1;
        // Datos de la fila del inventario (nombre de imagen y descripción, para ver/subir la foto)
        var fila = null;
        if ($.fn.dataTable.isDataTable('#tbl_inventario')) {
            $('#tbl_inventario').DataTable().rows().every(function () { if (!fila && this.data().ARTICULO == Id) fila = this.data(); });
        }

        // Limpiar todo antes de la respuesta
        $("#id_load_articulo").show();
        $("#id_nombre_articulo").text('Cargando…');
        $("#sacInvUnidad, #id_codigo_articulo, #lbl_unidad").text('');
        $("#id_precio_farmacia, #id_precio_mayorista, #id_precio_inst_pub, #id_precio_mayortista").text('C$ —');
        $("#id_reglas").html('');
        $("#sacInvTotal").text('—');
        $("#opcion1").prop('checked', false).val('');
        $("#sacInvRevision").addClass('d-none');
        $("#sacInvBodegas").html('<div class="sac-skel">' + Array(4).join('<div class="sac-skel-row"><i class="sac-skel-dot"></i><i style="flex:3"></i><i></i></div>') + '</div>');
        var ini = sacIniciales(fila ? fila.DESCRIPCION : Id);
        var imgTag = img ? '<img src="' + sacEsc(img) + '" alt="" onerror="this.remove()">' : '';
        $("#sacInvHeadThumb").html(imgTag + '<span>' + sacEsc(ini) + '</span>');
        // Texto de respaldo siempre debajo; la foto va encima y se quita si no carga
        $("#artImg").html('<span class="sac-imodal-ph">' + SAC_ICO_IMG + 'Imagen del producto</span>' + (img ?
            '<img class="img-fluid" src="' + sacEsc(img) + '" alt="Imagen del producto" arti="' + sacEsc(Id) + '" descr="' + sacEsc(fila ? fila.DESCRIPCION : '') + '" nombreImg="' + sacEsc(fila ? fila.IMG_NOMBRE : '') + '" title="Toca para ampliar" onerror="this.remove()">' : ''));

        bootstrap.Modal.getOrCreateInstance(document.querySelector(Selectors.id_mdl_info_producto)).show();

        $.get("getDataArticulo/" + Id, function (data) {
            if (req !== window.sacArtReq) return;
            var info = (data[0].InfoArticulo || [])[0] || {}, precios = data[0].NivelPrecio || [], bods = data[0].Bodega || [];

            $("#id_codigo_articulo").text(info.ARTICULO || Id);
            $("#lbl_unidad").text(info.UNIDAD || '');
            $("#id_nombre_articulo").text(String(info.DESCRIPCION || '').toUpperCase());
            $("#sacInvUnidad").text(info.UNIDAD ? ' · ' + info.UNIDAD : '');
            $("#sacInvHeadThumb span").text(sacIniciales(info.DESCRIPCION));

            var reglas = String(info.REGLAS || '0') === '0' ? [] : String(info.REGLAS).split(',').map(function (r) { return $.trim(r); }).filter(Boolean);
            $("#id_reglas").html(reglas.length ? reglas.map(function (r) { return '<span class="sac-tag sac-tag-info">' + sacEsc(r.replace(/\s*\+\s*/g, ' + ')) + '</span>'; }).join('') : '<span class="sac-muted">Sin bonificación</span>');

            $("#opcion1").val(info.ARTICULO || Id).prop('checked', info.cuarentena != null);
            $("#sacInvRevision").toggleClass('d-none', info.cuarentena == null);

            // Mismo orden de niveles que la versión anterior
            $("#id_precio_farmacia").text(sacPrecio(precios, 0));
            $("#id_precio_mayorista").text(sacPrecio(precios, 2));
            $("#id_precio_inst_pub").text(sacPrecio(precios, 1));
            $("#id_precio_mayortista").text(sacPrecio(precios, 3));

            var total = bods.reduce(function (a, b) { return a + sacNum(b.CANT_DISPONIBLE); }, 0);
            $("#sacInvTotal").text(numeral(total).format('0,0.00'));
            $("#sacInvBodegas").html(bods.length ? bods.map(sacBodegaHtml).join('') : '<p class="sac-bod-msg">Sin existencias registradas</p>');

            $("#id_load_articulo").hide();
        }).fail(function () {
            if (req !== window.sacArtReq) return;
            $("#id_nombre_articulo").text('No se pudo cargar el artículo');
            $("#sacInvBodegas").html('<p class="sac-bod-msg sac-err">Intenta de nuevo en unos segundos.</p>');
            $("#id_load_articulo").hide();
        });
    }

    var SAC_MORA_COLORES = ['#93C5FD', '#FACC15', '#F59E0B', '#EA580C', '#C2410C', '#7F1D1D'];
    function sacMoraBarra(vals) {
        var nums = vals.map(function (v) { return Math.max(0, sacNum(v)); });
        var total = nums.reduce(function (a, b) { return a + b; }, 0);
        $('#sac_mora_total').text(sacMoney(total));
        var html = total > 0 ? nums.map(function (v, i) {
            return v > 0 ? '<span style="width:' + (v / total * 100).toFixed(2) + '%;background:' + SAC_MORA_COLORES[i] + '"></span>' : '';
        }).join('') : '';
        $('#sac_mora_bar').html(html);
    }
    $('#tbl_historico_factura').on('draw.dt', function () {
        var n = $('#tbl_historico_factura').DataTable().page.info().recordsDisplay;
        $('#sac_fact_count').text(numeral(n).format('0,0') + (n === 1 ? ' factura' : ' facturas'));
    });

    // ===== Productos facturados (primera pestaña del modal de cliente) =====
    var sacPf = { cliente: null, req: 0, fpDesde: null, fpHasta: null };
    var sacPfHeader = [
        { "title": "", "data": "ARTICULO", "render": function (data, type, row) {
            if (type !== 'display') return [row.DESCRIPCION, row.ARTICULO, row.FACTURA, row.FECHA].join(' ');
            var bonif = sacNum(row.PRECIO_UNITARIO) <= 0;
            return '<div class="sac-fact">' +
                '<span class="sac-fact-main"><span class="sac-art-name-plain">' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</span>' +
                '<span class="sac-cli-sub">' + sacEsc(row.ARTICULO) + ' · Fact. ' + sacEsc(row.FACTURA) + ' · ' + sacFecha(row.FECHA) + '</span></span>' +
                (bonif ? '<span class="sac-tag sac-tag-info">Bonificado</span>' : '') +
                '<span class="sac-pf-cant"><span class="sac-cli-sub">Cant.</span> <strong class="sac-num">' + numeral(row.CANTIDAD).format('0,0') + '</strong>' +
                  (bonif ? '' : '<span class="sac-cli-sub d-block">' + sacMoney(row.PRECIO_UNITARIO) + ' c/u</span>') + '</span>' +
                '<strong class="sac-fact-amt sac-num">' + (bonif ? '—' : sacMoney(row.PRECIO_TOTAL)) + '</strong>' +
              '</div>';
        }},
        { "title": "Fecha", "data": "FECHA" }
    ];
    function sacPfIso(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }
    $(document).on('click', '#sacPfFiltrar', function () { sacPfCargar(); });

    function sacPfCargar() {
        if (!sacPf.cliente) return;
        var req = ++sacPf.req;
        var d1 = sacPf.fpDesde && sacPf.fpDesde.selectedDates[0], d2 = sacPf.fpHasta && sacPf.fpHasta.selectedDates[0];
        if ($.fn.dataTable.isDataTable('#tbl_prod_fact')) $('#tbl_prod_fact').DataTable().clear().draw();
        if (window.sacTableLoading) sacTableLoading('#tbl_prod_fact', true);
        $.get('getProductosFacturados/' + encodeURIComponent(sacPf.cliente), { desde: d1 ? sacPfIso(d1) : '', hasta: d2 ? sacPfIso(d2) : '' }, function (res) {
            if (req !== sacPf.req) return;
            initTable('#tbl_prod_fact', res.filas || [], sacPfHeader, [[1, 'desc']], [1]);
            $('#tbl_prod_fact_length, #tbl_prod_fact_filter').hide();
            var api = $('#tbl_prod_fact').DataTable();
            api.settings()[0].oLanguage.sEmptyTable = 'El cliente no tiene productos facturados en este periodo';
            api.search($('#tbl_buscar_prod_fact').val() || '').page.len(10).draw();
        }).fail(function () {
            if (req !== sacPf.req) return;
            if (window.sacTableError) sacTableError('#tbl_prod_fact', 'No se pudieron cargar los productos facturados.', sacPfCargar);
        });
    }
    // Coloca el calendario pegado al campo. El cálculo propio de flatpickr usa coordenadas de la
    // página, pero el calendario vive dentro del modal (fixed y con scroll), así que se desfasaba.
    function sacPfPosicion(fp, el) {
        var cal = fp.calendarContainer, ref = el || fp._positionElement;
        if (!cal || !ref) return;
        var host = cal.offsetParent || document.documentElement;
        var r = ref.getBoundingClientRect(), h = host.getBoundingClientRect();
        var ch = cal.offsetHeight, cw = cal.offsetWidth, vh = window.innerHeight, vw = window.innerWidth;
        var arriba = (vh - r.bottom) < ch + 8 && r.top > ch + 8;
        var top = (arriba ? r.top - ch - 4 : r.bottom + 4) - h.top + host.scrollTop;
        var left = Math.max(8, Math.min(r.left, vw - cw - 8)) - h.left + host.scrollLeft;
        cal.style.top = top + 'px'; cal.style.left = left + 'px'; cal.style.right = 'auto';
        cal.classList.toggle('arrowTop', !arriba); cal.classList.toggle('arrowBottom', arriba);
        cal.classList.add('arrowLeft'); cal.classList.remove('arrowRight', 'arrowCenter');
    }
    // Abre la pestaña para un cliente: limpia búsqueda, fija los últimos 90 días y consulta
    function sacPfIniciar(idCliente) {
        sacPf.cliente = idCliente;
        $('#tbl_buscar_prod_fact').val('');
        if (window.flatpickr && !sacPf.fpDesde) {
            var M = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
            var base = {
                locale: { weekdays: { shorthand: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'], longhand: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] },
                          months: { shorthand: M, longhand: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] },
                          firstDayOfWeek: 1, ordinal: function () { return ''; } },
                dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', maxDate: 'today', disableMobile: true,
                appendTo: document.getElementById('modal_info_cliente') || undefined,
                position: sacPfPosicion
            };
            sacPf.fpDesde = flatpickr('#sacPfDesde', $.extend({}, base, { onChange: function (d) {
                if (!d[0]) return; sacPf.fpHasta.set('minDate', d[0]);
            } }));
            sacPf.fpHasta = flatpickr('#sacPfHasta', $.extend({}, base, { onChange: function (d) {
                if (!d[0]) return; sacPf.fpDesde.set('maxDate', d[0]);
            } }));
        }
        if (sacPf.fpDesde) {
            var hoy = new Date(); hoy.setHours(0, 0, 0, 0); var ini = new Date(hoy); ini.setDate(ini.getDate() - 90);
            sacPf.fpDesde.set('maxDate', hoy); sacPf.fpHasta.set('minDate', ini);
            sacPf.fpDesde.setDate(ini, false); sacPf.fpHasta.setDate(hoy, false);
        }
        sacPfCargar();
    }

    function Modal_Cliente(Id){
        $("#id_load_cliente").show();
        var SAC_CHEV = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>';
        tbl_header_historico_factura =  [
                {"title": "","data": "FACTURA","className":'detalles-factura sac-fact-chev', "orderable": false, "render": function(data, type, row) {
                    return '<button type="button" class="sac-chev" aria-label="Ver detalle de la factura ' + sacEsc(row.FACTURA) + '" aria-expanded="false">' + SAC_CHEV + '</button>';
                }},
                {"title": "","data": "FACTURA", "render": function(data, type, row) {
                    if (type !== 'display') return row.FACTURA + ' ' + sacFecha(row.Dia);
                    var fv = $.trim(row.FECHA_VENCE || '');
                    var vence = fv && fv.replace(/[-\/\s]/g, '') ? sacEsc(fv) : '—';
                    return '<div class="sac-fact">' +
                        '<span class="sac-fact-main"><span class="sac-fact-no">Fact. ' + sacEsc(row.FACTURA) + '</span>' +
                        '<span class="sac-cli-sub">' + sacFecha(row.Dia) + '</span></span>' +
                        '<span class="sac-tag sac-tag-neutral">Vence ' + vence + '</span>' +
                        '<strong class="sac-fact-amt sac-num">' + sacMoney(row.Venta) + '</strong>' +
                      '</div>';
                }},
                {"title": "Dia","data": "Dia"}
                ]
        tbl_header_historico_3m =  [
                {"title": "","data": "FACTURA", "render": function(data, type, row) {
                    if (type !== 'display') return (row.DESCRIPCION || '') + ' ' + row.ARTICULO + ' ' + (row.Dia || '');
                    return '<div class="sac-fact">' +
                        '<span class="sac-fact-main"><span class="sac-art-name-plain">' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</span>' +
                        '<span class="sac-cli-sub">' + sacEsc(row.ARTICULO) + ' · Cant. ' + numeral(row.CANTIDAD).format('0,0.00') + ' · ' + sacEsc(row.Dia) + '</span></span>' +
                        '<strong class="sac-fact-amt sac-num">' + sacMoney(row.Venta) + '</strong>' +
                      '</div>';
                }},
                ]
        tbl_header_no_Facturado =  [
                {"title": "","data": "ARTICULO", "render": function(data, type, row) {
                    if (type !== 'display') return (row.DESCRIPCION || '') + ' ' + row.ARTICULO;
                    return '<div class="sac-fact">' +
                        '<span class="sac-fact-main"><span class="sac-art-name-plain">' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</span>' +
                        '<span class="sac-cli-sub">' + sacEsc(row.ARTICULO) + '</span></span>' +
                        '<span class="sac-cli-sub">Disponible <strong class="sac-num">' + numeral(row.total).format('0,0') + '</strong> ' + sacEsc(row.UNIDAD_ALMACEN) + '</span>' +
                      '</div>';
                }},
                ]

        // Cabecera y contacto del modal (se limpia antes de cargar el cliente nuevo)
        $('#sac_cli_avatar_txt').text('…');
        $('#lbl_nombre_cliente').text('Cargando…');
        $('#lbl_rutas, #lbl_codigo, #lbl_last_sale, #sac_cli_ruc, #sac_cli_tels, #sac_cli_dir').text('');
        $('#no_fact, #30_dias, #60_dias, #90_dias, #120_dias, #mas_120_dias').text('0.00');
        sacMoraBarra([0, 0, 0, 0, 0, 0]);
        $('#sac_fact_count').text('');

        // Montos, plan de crecimiento, buscadores y tablas: todo en blanco hasta que llegue la respuesta
        $('#lbl_limite, #lbl_saldo, #lbl_disponible').text('C$ —');
        $('#lbl_saldo').removeClass('sac-amt-bad sac-amt-warn sac-amt-zero');
        $('#lbl_acciones').text('—');
        $('#id_monto_base, #id_crecimiento_esperado, #id_crecimiento_minimo').text('C$ —');
        $('#id_porcent_crecimientio').text('— %');
        $('#id_ttmes').text('');
        $('#flexSwitchCheckDefault').prop('checked', false);
        $('#tbl_buscar_history_factura, #tbl_buscar_last3m, #tbl_search_nofacturado, #tbl_buscar_prod_fact').val('');
        var grafica = document.querySelector('.echart-sale-VentasDelMes');
        if (grafica && window.echarts && echarts.getInstanceByDom(grafica)) echarts.getInstanceByDom(grafica).clear();
        ['#tbl_historico_factura', '#tbl_ultm_3m', '#tbl_no_facturado'].forEach(function (sel) {
            if ($.fn.dataTable.isDataTable(sel)) {
                var api = $(sel).DataTable();
                api.search('');
                api.rows().every(function () { if (this.child.isShown()) this.child.hide(); });
                api.clear().draw();
            }
            if (window.sacTableLoading) sacTableLoading(sel, true);
        });
        // Siempre abre en la primera pestaña
        var tabPrimera = document.getElementById('prod-fact-tab');
        if (tabPrimera) bootstrap.Tab.getOrCreateInstance(tabPrimera).show();
        sacPfIniciar(Id);
        // Descarta respuestas de un cliente abierto antes (si se abren dos seguidos)
        var sacCliReq = window.sacCliReq = (window.sacCliReq || 0) + 1;

        $.get( "getDataCliente/" + Id, function( data ) {
            if (sacCliReq !== window.sacCliReq) return;

            $("#lbl_nombre_cliente").html(data[0].InfoCliente[0].NOMBRE)
            $("#id_load_cliente").hide();
            $("#lbl_rutas").html(data[0].InfoCliente[0].VENDEDOR)
            $("#lbl_codigo").html(data[0].InfoCliente[0].CLIENTE)
            
            var sacInfo = data[0].InfoCliente[0] || {};
            $("#lbl_last_sale").text(sacFecha(sacInfo.fecha))
            $("#sac_cli_avatar_txt").text(sacCliIniciales(sacInfo.NOMBRE))
            $("#sac_cli_avatar_dot").toggleClass('is-bad', sacMoroso(sacInfo)).toggleClass('is-ok', !sacMoroso(sacInfo))
            $("#sac_cli_ruc").text(sacCliRuc(sacInfo) ? ' · RUC ' + sacCliRuc(sacInfo) : '')
            $("#sac_cli_tels").text([$.trim(sacInfo.TELEFONO1 || ''), $.trim(sacInfo.TELEFONO2 || '')].filter(Boolean).join('  ') || '—')
            $("#sac_cli_dir").text(sacInfo.DIRECCION || '—')
            $("#lbl_saldo").removeClass('sac-amt-bad sac-amt-warn sac-amt-zero').addClass(sacSaldoCls(sacInfo.SALDO))

            $("#lbl_limite").html('C$ ' + numeral(data[0].InfoCliente[0].LIMITE_CREDITO).format('0,00.00'))
            $("#lbl_saldo").html('C$ ' + numeral(data[0].InfoCliente[0].SALDO).format('0,00.00'))
            $("#lbl_disponible").html('C$ ' + numeral(data[0].InfoCliente[0].CREDITODISP).format('0,00.00'))
            var acciones = data[0].Acciones;

            if (acciones > 0) {
                $("#lbl_acciones").html('<a href="https://carro.unimarksa.com/api/Perfil/' + Id + '" target="_blank">' + numeral(acciones).format('0,00') + '</a>');
            } else {
                $("#lbl_acciones").html('0');
            }

            $("#id_monto_base").html('C$ ' + numeral(data[0].PlanCrecimieto[0].InfoCliente.EVALUADO).format('0,00.00'))
            $("#id_crecimiento_esperado").html('C$ ' + numeral(data[0].PlanCrecimieto[0].InfoCliente.CRECIMIENTO).format('0,00.00'))
            $("#id_crecimiento_minimo").html('C$ ' + numeral(data[0].PlanCrecimieto[0].InfoCliente.COMPRA_MIN).format('0,00.00'))
            $("#id_porcent_crecimientio").html(numeral(data[0].PlanCrecimieto[0].InfoCliente.PROM_CUMP).format('0,00') + ' %')
            
            setBarVentasMes(data[0].PlanCrecimieto[0].SalesMonths)

            $("#flexSwitchCheckDefault").attr('checked',data[0].PlanCrecimieto[0].isPlan);
            $("#id_ttmes").html('TOTAL: C$ ' +numeral(data[0].PlanCrecimieto[0].ttactual).format('0,00'))

    


            var isMora  = isValue(data[0].ClienteMora[0],'N/D',true)

            if(isMora!='N/D'){
                $("#no_fact").html(numeral(data[0].ClienteMora[0].NoVencidos).format('0,00.00'))
                $("#30_dias").html(numeral(data[0].ClienteMora[0].Dias30).format('0,00.00'))
                $("#60_dias").html(numeral(data[0].ClienteMora[0].Dias60).format('0,00.00'))
                $("#90_dias").html(numeral(data[0].ClienteMora[0].Dias90).format('0,00.00'))
                $("#120_dias").html(numeral(data[0].ClienteMora[0].Dias120).format('0,00.00'))
                $("#mas_120_dias").html(numeral(data[0].ClienteMora[0].Mas120).format('0,00.00'))
                var m = data[0].ClienteMora[0];
                sacMoraBarra([m.NoVencidos, m.Dias30, m.Dias60, m.Dias90, m.Dias120, m.Mas120]);
            }

            initTable('#tbl_historico_factura',data[0].ClientesHistoricoFactura,tbl_header_historico_factura,[[2, "DESC"]],[2]);
            initTable('#tbl_ultm_3m',data[0].Historico3M,tbl_header_historico_3m,[[0, "asc"]],[]);
            initTable('#tbl_no_facturado',data[0].ArticulosNoFacturado,tbl_header_no_Facturado,[[0, "asc"]],[]);

            $("#tbl_historico_factura_length").hide();
            $("#tbl_historico_factura_filter").hide();

            $("#tbl_ultm_3m_length").hide();
            $("#tbl_ultm_3m_filter").hide();

            $("#tbl_no_facturado_length").hide();
            $("#tbl_no_facturado_filter").hide();

            $('#tbl_historico_factura, #tbl_ultm_3m, #tbl_no_facturado').each(function () { $(this).DataTable().page.len(10).draw(); });

        }).fail(function () {
            if (sacCliReq !== window.sacCliReq) return;
            $('#lbl_nombre_cliente').text('No se pudo cargar el cliente');
            $('#sac_cli_avatar_txt').text('—');
            $('#id_load_cliente').hide();
            var msgs = {
                '#tbl_historico_factura': 'No se pudo cargar el histórico de facturas.',
                '#tbl_ultm_3m': 'No se pudieron cargar las compras de los últimos 3 meses.',
                '#tbl_no_facturado': 'No se pudieron cargar los artículos no facturados.'
            };
            if (window.sacTableError) Object.keys(msgs).forEach(function (sel) { sacTableError(sel, msgs[sel], function () { Modal_Cliente(Id); }); });
        })

        function setBarVentasMes(Data){

            dta_ventas_mercados = {
                dataset: {
                VentasDelMes:   [[],[],[]],
                }
            };

            $.each(Data, function (i, d) {
                dta_ventas_mercados.dataset['VentasDelMes'][0].push(numeral(d.ttMonth).format('00.00'))
                dta_ventas_mercados.dataset['VentasDelMes'][1].push(0)
                dta_ventas_mercados.dataset['VentasDelMes'][2].push(d.name_month + '/' + d.annio)
            });
            
            Build_Echart_bar(dta_ventas_mercados)
            
        }

        function abbrNum(number, decPlaces) {    
            decPlaces = Math.pow(10,decPlaces);
            var abbrev = [ " K", " M", " B", " T" ];
            for (var i=abbrev.length-1; i>=0; i--) {
                var size = Math.pow(10,(i+1)*3);
                if(size <= number) {
                    number = Math.round(number*decPlaces/size)/decPlaces;
                    if((number == 1000) && (i < abbrev.length - 1)) {
                        number = 1;
                        i++;
                    }
                    number += abbrev[i];
                    break;
                }
            }

            return number;
        }

        function Build_Echart_bar(data) {

      

var tooltipFormatter = function tooltipFormatter(params) {
    return `<div class="card">
              <div class="card-header bg-light py-2">
                <h6 class="text-600 mb-0">`+params[0].axisValue+ `</h6>
              </div>
              <div class="card-body py-2">
                <h6 class="text-600 mb-0 fw-normal">
                  <span class="fas fa-circle text-primary me-2"></span>C$ 
                  <span class="fw-medium">`+ numeral(params[0].data).format('0,00.00')+` </span>
                </h6>
                </div>
            </div>`;
};




            var getOptionSales = function getOptionSales(data1, data2, data3) {
                return function () {
                    return {
                        color: utils.getGrays().white,
                        tooltip: {
                        trigger: 'axis',
                        padding: 0,
                        backgroundColor: 'transparent',
                        borderWidth: 0,
                        transitionDuration: 0,
                        position: function position(pos, params, dom, rect, size) {
                            return getPosition(pos, params, dom, rect, size);
                        },
                        
                        axisPointer: {
                            type: 'none'
                        },
                        formatter: tooltipFormatter
                        },
                        xAxis: {
                            type: 'category',
                            data: data3,
                            axisLabel: {
                                color: utils.getGrays()['600'],
                            },
                        axisLine: {
                            lineStyle: {
                            color: utils.getGrays()['300'],
                            type: 'dashed'
                            }
                        },
                        axisTick:false,
                        boundaryGap: true
                        },
                        yAxis: {
                        position: 'right',
                        axisPointer: {
                            type: 'none'
                        },
                        axisTick: 'none',
                        splitLine: {
                            show: false
                        },
                        axisLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        }
                        },
                        
                        series: [{
                            type: 'bar',
                            name: 'Revenue',
                            data: data1,
                            lineStyle: {
                                color: utils.getColor('primary')
                            },
                            label: {
                                show: true,
                                position: 'top',
                                formatter: function(d) {
                                return "C$ " + abbrNum(d.data,2);
                                }
                            },
                            
                            itemStyle: {
                                barBorderRadius: [4, 4, 0, 0],
                                color: utils.getColor('primary'),
                                borderColor: utils.getGrays()['300'],
                                borderWidth: 1
                            },
                            emphasis: {
                                itemStyle: {
                                color: utils.getColor('primary')
                                }
                            }
                        }, {
                        type: 'line',
                        name: 'Optimo',
                        data: data2,
                        symbol: 'circle',
                        symbolSize: 6,
                        animation: false,
                        itemStyle: {
                            color: utils.getColor('warning')
                        },
                        
                        lineStyle: {
                            type: 'dashed',
                            width: 2,
                            color: utils.getColor('warning')
                        }
                        }],
                        grid: {
                        right: 5,
                        left: 5,
                        bottom: '8%',
                        top: '5%'
                        }
                    };
                };
            };

            var initChart = function initChart(el, options) {
                var userOptions = utils.getData(el, 'options');
                var chart = window.echarts.init(el);
                echartSetOption(chart, userOptions, options);
            };

            var chartKeys = ['VentasDelMes'];
            chartKeys.forEach(function (key) {
                var el = document.querySelector(".echart-sale-".concat(key));
                el && initChart(el, getOptionSales(
                data.dataset[key][0], 
                data.dataset[key][1], 
                data.dataset[key][2]
                ));
            });
            };
        var id_mdl_info_cliente = document.querySelector(Selectors.id_mdl_info_cliente);
        var modal = bootstrap.Modal.getOrCreateInstance(id_mdl_info_cliente);
        modal.show();
    }

    function getStackIcon(icon, transform) {
        return `<span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="${icon} fa-stack-1x text-primary" data-fa-transform=${transform}></i>
                </span>
                `;
        
    };

    function promocionLeerMas(id){
        $.ajax({
            type: "GET",
            url: 'getDataPromocion', 
            async: false,
            dataType: "json",
            success: function(data){
                $.each(data,function(key, registro) {
                   if(id == registro.id){
                    Swal.fire({
                        title: registro.titulo,
                        html: '<div style="text-align: justify">'+registro.descripcion.replace(/\n/g, '</br>')+'</div>'
                        
                   })
                   }
                }); 	 
                
            },
            error: function(data) {
                //alert('error');
            }
        });    

    }

    $(document).on('click', '.img-fluid', function (e) {
        articulo = $(this).attr('arti');
        descripcion = $(this).attr('descr');
        nombreImg = $(this).attr('nombreImg');
        user = $('#userId').val();
        
        if(nombreImg == "item.png" && user == 1){
            Swal.fire({
            title: '¡Desea ingresar una imagen para este articulo!',
            text: "¿Desea continuar con esta acción?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si!',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                Swal.fire({
                    title: 'Ingrese una Imagen',
                    html: '<form role="form" action="imgArticulo" method="post" enctype="multipart/form-data">'+
                            '@csrf'+
                            '<input type="hidden" value="'+articulo+'" name="sku">'+
                            '<input type="hidden" value="'+descripcion+'" name="nombre">'+
                            '<div class="form-group">'+
                            '<div class="panel">SUBIR IMAGEN</div>'+
                            '<input type="file" class="nuevaImagen" name="nuevaImagen" required>'+
                            '</div>'+
                            '<div class="modal-footer">'+
                            '<div class="col-md-12 text-center">'+
                            '<button type="submit" class="btn btn-primary">Subir</button>'+
                            '</div>'+
                            '</div>'+
                        '</form>',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ok!',
                    showConfirmButton: false,
                    showLoaderOnConfirm: true,
                    preConfirm: (result) => {
                                              
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
                
            },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }else{
    
            url_image = $(this).attr('src');
            Swal.fire({
                showCloseButton: true,
                closeButtonColor: '#d33',
                showConfirmButton: false,
                imageUrl: url_image,
                imageAlt: 'Custom image',
            })

            $(".swal2-popup").css('width', '50%');
        }
    })   

    $(".nuevaImagen").change(function(){

        var imagen = this.files[0];

        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function(event){

            var rutaImagen = event.target.result;

            $(".previsualizar").attr("src", rutaImagen);

        })

    })

    $("#opcion1").click(function(){
        Articulo = $(this)[0];
        if(Articulo.checked){
            $.ajax({
                url: "insertCuarentena",
                type: 'post',
                data: {                
                    Articulo    : $(Articulo).val(),
                    Opcion      : 1,      
                    _token      : "{{ csrf_token() }}" 
                },
                async: true,
                success: function(response) {
                    Swal.fire("Guardar", "El producto ha sido puesto en revisión", "success");
                },
                error: function(response) {
                    
                }
            }).done(function(data) {
                
            });
        }else{
            $.ajax({
                url: "insertCuarentena",
                type: 'post',
                data: {                
                    Articulo   : $(Articulo).val(),
                    Opcion      : 2,    
                    _token      : "{{ csrf_token() }}" 
                },
                async: true,
                success: function(response) {
                    Swal.fire("Correcto", "El producto ya no esta en revisión", "success");
                },
                error: function(response) {
                    
                }
            }).done(function(data) {
                
            });   
        }
    })
    
</script>
