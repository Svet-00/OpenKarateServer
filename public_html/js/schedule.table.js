$(function () {
  const dayOfWeekOptions = [
    'Понедельник',
    'Вторник',
    'Среда',
    'Четверг',
    'Пятница',
    'Суббота',
    'Воскресенье',
  ]
  const gymsColumnDefs = [
    {
      data: 'id',
      visible: false,
      searchable: false,
      type: 'hidden',
    },
    {
      data: 'day_of_week',
      title: 'День недели',
      type: 'select',
      options: dayOfWeekOptions,
      render: function (data, type, row) {
        if (type === 'sort' || type === 'type') {
          return dayOfWeekOptions.indexOf(data)
        }
        return data
      },
      multiple: false,
      select2: { width: '100%', language: 'ru' },
    },
    {
      data: 'time',
      title: 'Время',
    },
    {
      data: 'description',
      title: 'Описание',
    },
  ]

  var schedulesDataTable

  var scheduleTable = $('#scheduleTable')
  var urlGet = scheduleTable.data('getUrl')
  var urlEdit = scheduleTable.data('editUrl')
  var urlAdd = scheduleTable.data('addUrl')
  var urlDelete = scheduleTable.data('deleteUrl')

  schedulesDataTable = scheduleTable.DataTable({
    scrollX: true,
    language: {
      url: $('.table-responsive').data('translationUrl'),
    },
    sPaginationType: 'full_numbers',
    data: {},
    columns: gymsColumnDefs,
    dom: 'Bfrtip',
    select: {
      style: 'single',
      items: 'row',
    },
    responsive: false,
    altEditor: true,
    buttons: [
      {
        text: 'Добавить',
        name: 'add',
      },
      {
        extend: 'selected',
        text: 'Редактировать',
        name: 'edit',
      },
      {
        extend: 'selected',
        text: 'Удалить',
        name: 'delete',
      },
    ],
    onAddRow: function (datatable, rowdata, success, error) {
      rowdata.gym_id = currentGymId
      $.ajax({
        url: urlAdd,
        method: 'POST',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
    onDeleteRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlDelete.replace('id', rowdata.id),
        method: 'DELETE',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
    onEditRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlEdit.replace('id', rowdata.id),
        method: 'POST',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
  })

  var gymSelect = $('#gymSelect')
  var scheduleData = {}
  // stores id of selected gym
  var currentGymId = null

  gymSelect.select2({
    width: '100%',
  })
  if (gymSelect.select2('data').length == 0) {
    $('#schedulesTableWrapper').addClass('border-left-info')
    $('#schedulesTableContainer').hide()
    $('#noGymInfo').show()
  } else {
    var selectedId = gymSelect.find(':selected')[0].value
    setScheduleData(selectedId)
  }

  gymSelect.on('select2:select', function (e) {
    var id = e.params.data.id
    setScheduleData(id)
  })

  function setScheduleData(gymId) {
    schedulesDataTable.clear()
    schedulesDataTable.draw()
    currentGymId = gymId

    if (scheduleData[gymId] == null) {
      $.ajax({
        url: urlGet.replace('id', gymId),
        dataType: 'json',
        success: function (data, status, jqXHR) {
          scheduleData[gymId] = data
          setScheduleData(gymId)
        },
        error: function (jqXHR, status, error) {
          console.log(error)
        },
      })
    } else {
      schedulesDataTable.rows.add(scheduleData[gymId])
      schedulesDataTable.draw()
    }
  }
})
