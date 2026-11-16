<script type="text/javascript">
 

    
    var dta_calendar = []  
    var table = ''
    var nMes   = $("#IdSelectMes option:selected").val();           
    var annio  = $("#IdSelectAnnio option:selected").val()
    moment.lang('es', {
            months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
            monthsShort: 'Ene._Feb._Mar_Abr._May_Jun_Jul._Ago_Sept._Oct._Nov._Dic.'.split('_'),
            weekdays: 'Domingo_Lunes_Martes_Miercoles_Jueves_Viernes_Sabado'.split('_'),
            weekdaysShort: 'Dom._Lun._Mar._Mier._Jue._Vier._Sab.'.split('_'),
            weekdaysMin: 'Do_Lu_Ma_Mi_Ju_Vi_Sa'.split('_')
        }
    );
    var RUTA  = $("#id_ruta").text();  
    getDataCalendar(RUTA)

   

    $("#AddVisita").click(function(){
        var slCli   = $("#sclCliente option:selected").val();  
        var dtIni   = $("#StartDate").val();
        var Descr   = $("#Description").val(); 
        var Ruta_   = $("#id_ruta").text(); 

        


        if (slCli > 0) {

            $.ajax({
            url: "../AddVisita",
            type: 'post',
            data: {
                Cliente   : slCli,
                FechaVi   : dtIni,
                Descrip   : Descr,
                Ruta      : Ruta_,
                _token    : "{{ csrf_token() }}" 
            },
            async: true,
            success: function(response) {
                if(response){
                    Swal.fire({
                    title: 'Correcto',
                    icon: 'success',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }   
                    })
                }else{
                    Swal.fire('Visita repetida',"Ya tiene agendada una visita para este día",'warning');
                }
            },
            error: function(response) {
                
            }
            }).done(function(data) {
                //location.reload();
            });
            
        } else {
            Swal.fire("OOPS :( ", "Cliente no Seleccionado!", "error");
        }
    })

    $("#btnReutilizar").click(function(){ 

        var calendar_title = $(".calendar-title").text();
        var RUTA  = $("#id_ruta").text(); 

        let datepicker

      



        Swal.fire({
        title: 'Reutilizar Plan de Trabajo',
        text: "¿Desea continuar con esta acción?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        showLoaderOnConfirm: true,
        preConfirm: () => {

            Swal.fire({
                title: 'Defina el Rango de Fecha a Reutilizar',
                html:
                    '<input id="start-date" class="form-control swal2-input" type="text" placeholder="Fecha de inicio">' +
                    '<input id="end-date" class="form-control swal2-input" type="text" placeholder="Fecha de fin">',
                    inputValue: moment().format('YYYY-MM-DDTHH:mm:ss'), // Formato MySQL
                inputAttributes: {
                    autocapitalize: 'off'
                },
                preConfirm: () => {
                    const startDateInput = document.getElementById('start-date');
                    const endDateInput = document.getElementById('end-date');

                    const startDate = moment(startDateInput.value, 'YYYY-MM-DD');
                    const endDate = moment(endDateInput.value, 'YYYY-MM-DD');

                    if (!startDateInput.value || !endDateInput.value) {
                        Swal.showValidationMessage('Ambas fechas son requeridas');
                        return false;
                    }

                    if (!startDate.isValid() || !endDate.isValid() || startDate.isSameOrAfter(endDate)) {
                        Swal.showValidationMessage('Las fechas no son válidas');
                        return false;
                    }

                    return [startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD')];
                },
                didOpen: () => {
                    new Pikaday({ field: document.getElementById('start-date') });
                    new Pikaday({ field: document.getElementById('end-date') });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const [startDate, endDate] = result.value;

                    $.ajax({
                        url: "../reutilizar",
                        type: 'POST',
                        data:{
                                startDate    : startDate,
                                endDate      : endDate,
                                ruta         : RUTA,
                                _token       : "{{ csrf_token() }}" 
                            },
                        async: true,
                        success: function(response) {
                            Swal.fire({
                            title: 'Ajuste realizados',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'OK'
                            }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                                }
                            })
                        },
                        error: function(response) {
                        }
                    }).done(function(data) {
                        
                    });


                }
            });
            
        

        },
            allowOutsideClick: () => !Swal.isLoading()
        });

    })
    
    appCalendarInit(dta_calendar)
    
    function soloNumeros(caracter, e, numeroVal) {
        var numero = numeroVal;
        if (String.fromCharCode(caracter) === "." && numero.length === 0) {
            e.preventDefault();
            swal.showValidationError('No se puede iniciar con un punto');
        } else if (numero.includes(".") && String.fromCharCode(caracter) === ".") {
            e.preventDefault();
            swal.showValidationError('No puede haber mas de dos puntos');
        } else {
            const soloNumeros = new RegExp("^[0-9]+$");
            if (!soloNumeros.test(String.fromCharCode(caracter)) && !(String.fromCharCode(caracter) === ".")) {
                e.preventDefault();
                swal.showValidationError(
                    'No se pueden escribir letras, solo se permiten datos númericos'
                );
            }
        }
    }
  
    /*-----------------------------------------------
    |   Calendar
    -----------------------------------------------*/
    function getStackIcon(icon, transform) {
        return `<span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="${icon} fa-stack-1x text-primary" data-fa-transform=${transform}></i>
                </span>
                `;
        
    };
    function getTemplate(event) {
      
        return ``;
    };

    function appCalendarInit(events) {
        var Selectors = {
            ACTIVE: '.active',
            ADD_EVENT_FORM: '#addEventForm',
            ADD_EVENT_MODAL: '#addEventModal',
            CALENDAR: '#appCalendar',
            CALENDAR_TITLE: '.calendar-title',
            DATA_CALENDAR_VIEW: '[data-fc-view]',
            DATA_EVENT: '[data-event]',
            DATA_VIEW_TITLE: '[data-view-title]',
            EVENT_DETAILS_MODAL: '#eventDetailsModal',
            EVENT_DETAILS_MODAL_CONTENT: '#eventDetailsModal .modal-content',
            EVENT_START_DATE: '#addEventModal [name="startDate"]',
            EVENT_END_DATE: '#addEventModal [name="endDate"]',


            INPUT_TITLE: '[name="title"]'
        };

        var _window3 = window,dayjs = _window3.dayjs;
        var currentDay = dayjs && dayjs().format('DD');
        var currentMonth = dayjs && dayjs().format('MM');
        var prevMonth = dayjs && dayjs().subtract(1, 'month').format('MM');
        var nextMonth = dayjs && dayjs().add(1, 'month').format('MM');
        var currentYear = dayjs && dayjs().format('YYYY');

        

       
        var Events = {
            CLICK: 'click',
            SHOWN_BS_MODAL: 'shown.bs.modal',
            SUBMIT: 'submit'
        };
        var DataKeys = {
            EVENT: 'event',
            FC_VIEW: 'fc-view'
        };
        var ClassNames = {
            ACTIVE: 'active'
        };
        var eventList = events.reduce(function (acc, val) {

            return val.schedules ? acc.concat(val.schedules.concat(val)) : acc.concat(val);
        }, []);

        var updateTitle = function updateTitle(title) {

            document.querySelector(Selectors.CALENDAR_TITLE).textContent = title;
            
        };

        var appCalendar = document.querySelector(Selectors.CALENDAR);
        var addEventForm = document.querySelector(Selectors.ADD_EVENT_FORM);
        var addEventModal = document.querySelector(Selectors.ADD_EVENT_MODAL);
        var eventDetailsModal = document.querySelector(Selectors.EVENT_DETAILS_MODAL);

        if (appCalendar) {

            var calendar = renderCalendar(appCalendar, {
                headerToolbar: false,
                dayMaxEvents: 2,
                height: 800,
                locale: 'es',
                stickyHeaderDates: false,
                initialView: 'timeGridWeek',
                firstDay: 1, 
                views: {
                    week: {
                    eventLimit: 3
                    }
                },
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: true,
                    meridiem: true
                },
                events: eventList,
                eventClick: function eventClick(info) {


                    var color = "bg-soft-info";
                    
                    if(info.event.extendedProps.efectiva ==  2){
                        color = 'bg-soft-danger';
                    }else{
                        if(info.event.extendedProps.efectiva ==  1) {
                            color = 'bg-soft-success';
                        }
                    }


                    
                    $("#id_modal_header").addClass(color);

                    var horaInicio = moment(info.start);
                    var desripcion = isValue(info.event.extendedProps.description,'N/D',true).split(' ').slice(0, 1000).join(' ')
                    var efect = isValue(info.event.extendedProps.efectiva,0,true)
                    var orden = isValue(info.event.extendedProps.orden,'',true).split(';')
                    
                    var ordenes = '';

                    if(orden != ''){
                        orden.forEach(function (item){
                            var ordenS = item.split('/')
                           
                            ordenes += '<div class="col-sm-6">'+ordenS[0].replace('[',"")+'</div>'+
                            '<div class="col-sm-6" style="text-align: right">'+ordenS[1].replace(']',"")+'</div>'
                        })
                        $("#eventOrden").show();                   
                    }else{
                        $("#eventOrden").hide();
                    }

                    $("#id_event").text(info.event.extendedProps.id_event)   
                    $("#id_lbl_title_event").text(moment(info.start).locale('es').format("dddd, MMM D, YYYY"));
                    $("#NameClient").val(info.event.title);
                    $("#eventDescription").val(desripcion);

                    var dtIni = isValue(info.event.extendedProps.dtIni,'N/D',true)
                    var dtEnd = isValue(info.event.extendedProps.dtEnd,'N/D',true)


                    dtInit = (dtIni === 'N/D')? horaInicio.format("HH:mm"):  moment(dtIni, "HH:mm:ss").format("HH:mm");
                    dtEnd  = (dtEnd === 'N/D')? horaInicio.add(45, 'minutes').format("HH:mm") : moment(dtEnd, "HH:mm:ss").format("HH:mm")

                    $("#eventLabel").val(efect).change();
                    $("#timepicker_ini").val(dtInit);
                    $("#timepicker_end").val(dtEnd);

                    $('#ordenes')
                        .empty()
                        .append(ordenes);
                    
                    var modal = new window.bootstrap.Modal(eventDetailsModal);
                    modal.show();

                },
                dateClick: function dateClick(info) {
                    var day_ = new Date(info.dateStr);

                    var flatpickr = document.querySelector(Selectors.EVENT_START_DATE)._flatpickr;

                    flatpickr.setDate([info.dateStr]);

                    var modal = new window.bootstrap.Modal(addEventModal);
                    modal.show();

                },
                editable: true,
                eventDrop: function(info) {
                    var eventId            = info.event.extendedProps.id_event;
                    var newStartDate       = info.event.start;                
                    var eventDate          = moment(newStartDate).format('YYYY-MM-DD HH:mm:ss');

                    var eventData = {
                        id: eventId,
                        date: eventDate,
                        _token  : "{{ csrf_token() }}" 
                    };

                    $.ajax({
                        type: 'POST',
                        url: '../UpdateVisita', 
                        data: eventData,
                        success: function(response) {
                        },
                        error: function(error) {
                        }
                    });


                }
            });

            updateTitle(calendar.currentData.viewTitle);
            document.querySelectorAll(Selectors.DATA_EVENT).forEach(function (button) {
            button.addEventListener(Events.CLICK, function (e) {
                var el = e.currentTarget;
                var type = utils.getData(el, DataKeys.EVENT);

                switch (type) {
                case 'prev':
                    calendar.prev();
                    updateTitle(calendar.currentData.viewTitle);
                    break;

                case 'next':
                    calendar.next();
                    updateTitle(calendar.currentData.viewTitle);
                    break;

                case 'today':
                    calendar.today();
                    updateTitle(calendar.currentData.viewTitle);
                    break;

                default:
                    calendar.today();
                    updateTitle(calendar.currentData.viewTitle);
                    break;
                }
            });
            });

            document.querySelectorAll(Selectors.DATA_CALENDAR_VIEW).forEach((link) => {

                link.addEventListener(Events.CLICK, (e) => {
                    e.preventDefault();
                    
                    const el = e.currentTarget;
                    const text = el.textContent;

                    el.parentElement.querySelector(Selectors.ACTIVE).classList.remove(ClassNames.ACTIVE);
                    el.classList.add(ClassNames.ACTIVE);
                    
                    document.querySelector(Selectors.DATA_VIEW_TITLE).textContent = text;

                    calendar.changeView(utils.getData(el, DataKeys.FC_VIEW));
                    
                    updateTitle(calendar.currentData.viewTitle);
                });
            });

            
            
        }

        addEventModal && addEventModal.addEventListener(Events.SHOWN_BS_MODAL, function (_ref13) {
            var currentTarget = _ref13.currentTarget;
            //currentTarget.querySelector(Selectors.INPUT_TITLE).focus();
        });
    }


    function getDataCalendar(Ruta){
        var annio = moment().format('M');
        dta_calendar = []

        $.ajax({
            type: "GET",
            url: '../getDataVisita/' + Ruta, 
            async: false,
            dataType: "json",
            success: function(data){
                $.each(data,function(key, registro) {
                    
                    var color = "bg-soft-info";

                    if(registro.efectiva ==  2){
                        color = 'bg-soft-danger';
                    }else{
                        if(registro.efectiva ==  1) {
                            color = 'bg-soft-success';
                        }
                    }

                    dta_calendar.push({
                            'id_event'      : registro.id,
                            'nombre'        : registro.nombre,
                            'title'         : registro.nombre,
                            'cliente'       : registro.cliente,
                            'start'         : registro.fechaInicio,
                            'description'   : registro.descripcion,
                            'efectiva'      : registro.efectiva,
                            'dtIni'         : registro.time_ini,
                            'dtEnd'         : registro.time_end,
                            'className'     : color,
                            'orden'         : registro.orden
                        })

                    
                }); 	 
                
            },
            error: function(data) {
                //alert('error');
            }
        });
        
        return dta_calendar;
        
    }

    function ConfirmedVisita(){
        var id              =  $("#id_event").text();
        var isEfective      = $("#eventLabel option:selected").val(); 
        var timepicker_ini  =  $("#timepicker_ini").val();
        var timepicker_end  =  $("#timepicker_end").val();

        $.ajax({
            url: "../CheckVisita",
            type: 'POST',
            data:{
                id          : id,
                startDate   : timepicker_ini,
                endDate     : timepicker_end,
                isEfective  : isEfective,
                _token      : "{{ csrf_token() }}" 
                },
            async: true,
            success: function(response) {
                Swal.fire({
                title: 'Ajustado',
                icon: 'success',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK'
                }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                    }
                })
            },
            error: function(response) {
            }
        }).done(function(data) {
            
        });

    }
    function rmVisita(){
        var id =  $("#id_event").text();

        Swal.fire({
        title: '¡Se removera la visita para este Cliente!',
        text: "¿Desea continuar con esta acción?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            $.ajax({
                url: "../rmVisita/"+id,
                type: 'GET',
                async: true,
                success: function(response) {
                    Swal.fire({
                    title: 'Removido',
                    icon: 'success',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                        }
                    })
                },
                error: function(response) {
                }
            }).done(function(data) {
                
            });
        },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }

   

  
</script>
