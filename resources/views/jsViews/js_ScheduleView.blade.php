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
    var RUTA  = $("#sclVendedor option:selected").val();  
    getDataCalendar(RUTA)


    $("#AddVisita").click(function(){
        var slCli   = $("#sclCliente option:selected").val();  
        var dtIni   = $("#StartDate").val();
        var Descr   = $("#Description").val(); 


        if (slCli > 0) {

            $.ajax({
            url: "AddVisita",
            type: 'post',
            data: {
                Cliente   : slCli,
                FechaVi   : dtIni,
                Descrip   : Descr,
                _token  : "{{ csrf_token() }}" 
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
        return `
        <div class="modal-header bg-light ps-card pe-5 border-bottom-0">
        <div>
            <h5 class="modal-title mb-0">${event.title}</h5>
        </div>
        <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-card pb-card pt-1 fs--1">
        
            <div class="d-flex mt-3">
                ${getStackIcon('fas fa-align-left')}
                <div class="flex-1">
                <h6>Descripción</h6>
                <p class="mb-0">
                ${isValue(event.extendedProps.description,'N/D',true).split(' ').slice(0, 1000).join(' ')}
                </p>
                </div>
            </div>
            <div class="d-flex mt-3">
                ${getStackIcon('fas fa-calendar-check')}
                <div class="flex-1">
                    <h6>Fecha de visita</h6>
                    <p class="mb-1">
                    ${ moment(event.start).locale('es').format("dddd, MMMM D, YYYY") } 
                    </p>
                </div>
            </div>
        </div>
        </div>
        <div class="modal-footer d-flex justify-content-end bg-light px-card border-top-0">
        </div>
        `;
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
               

                    var descr = isValue(info.event.extendedProps.description,'N/D',true).split(' ').slice(0, 1000).join(' ')
                    var dtIni = isValue(info.event.extendedProps.dtIni,'N/D',true)
                    var dtEnd = isValue(info.event.extendedProps.dtEnd,'N/D',true)
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

                    $("#id_lbl_title_event").text(moment(info.start).locale('es').format("dddd, MMM D, YYYY"));
                    $("#NameClient").val(info.event.title);
                    $("#eventDescription").val(descr);
                    
                    
                   
                    //$("#eventLabel").val(efect).change();
                    $("#eventLabel").hide();

                    //$("#timepicker_ini").val(dtIni);                    
                    //$("#timepicker_end").val(dtEnd);
                    $("#timepicker_ini").hide();                    
                    $("#timepicker_end").hide();

                    $('#ordenes')
                        .empty()
                        .append(ordenes);
                    

                    var modal = new window.bootstrap.Modal(eventDetailsModal);
                    modal.show();

                },
                dateClick: function dateClick(info) {
                  

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
                        url: 'UpdateVisita', 
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

    $("#crm-schedule-tab").click(function(){
        setTimeout(function() {
            $('#btnToday').trigger('click');
        }, 1000);
    })

    $('#sclVendedor').on('change', function(){
        getDataCalendar(this.value)
    });
    function getDataCalendar(Ruta){
        var annio = moment().format('M');
        dta_calendar = []

        $.ajax({
            type: "GET",
            url: 'getDataVisita/' + Ruta, 
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
                            'orden'         : registro.orden,
                            'className'     : color
                        })

                    
                }); 	 
                
            },
            error: function(data) {
                //alert('error');
            }
        });
        appCalendarInit(dta_calendar)
        //return dta_calendar;
    }
</script>
