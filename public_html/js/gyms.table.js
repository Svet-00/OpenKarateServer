$(function () {
  var gymsColumnDefs = [
    {
      data: 'id',
      visible: false,
      searchable: false,
      type: 'hidden',
    },
    {
      data: 'title',
      title: 'Заголовок',
      unique: true,
      required: true,
    },
    {
      data: 'address',
      title: 'Адрес',
      required: true,
    },
    {
      data: 'description',
      title: 'Описание',
      type: 'textarea',
    },
    {
      data: 'working_time',
      title: 'Время работы',
      type: 'textarea',
    },
    {
      data: 'phone_number',
      title: 'Номер телефона',
    },
    {
      data: 'email',
      title: 'Email',
    },
    {
      data: 'vk_link',
      title: 'Ссылка ВК',
    },
  ]

  var gymPicturesColumnDefs = [
    {
      data: 'id',
      visible: false,
      searchable: false,
      type: 'hidden',
    },
    {
      data: 'url',
      title: 'Изображение',
      searchable: false,
      render: function (data, type, row) {
        if (data)
          return (
            "<img style='max-width:300px;max-height:300px' src=" +
            data +
            '></img>'
          )
      },
      type: 'file',
      accept: 'image/jpeg,image/png',
    },
  ]

  var gymsDataTable, gymPicturesDataTable

  var gymsTable = $('#gymsTable')
  var urlGetGyms = gymsTable.data('getUrl')
  var urlAddGym = gymsTable.data('addUrl')
  var urlEditGym = gymsTable.data('editUrl')
  var urlDeleteGym = gymsTable.data('deleteUrl')

  var gymPicturesTable = $('#gymPicturesTable')
  var urlAddPicture = gymPicturesTable.data('addUrl')
  var urlDeletePicture = gymPicturesTable.data('deleteUrl')

  var gymPicturesTableContainer = $('#gymPicturesTableContainer')
  gymPicturesTableContainer.hide()

  var noGymSelectedInfo = $('#noGymSelectedInfo')
  var gymPicturesCardBody = $('#gymPicturesCardBody')

  gymsDataTable = gymsTable.DataTable({
    scrollX: true,
    language: {
      url: $('.table-responsive').data('translationUrl'),
    },
    sPaginationType: 'full_numbers',
    ajax: {
      url: urlGetGyms,
      dataSrc: '',
    },
    columns: gymsColumnDefs,
    dom: 'Bfrtip',
    select: 'single',
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
      {
        text: 'Обновить',
        name: 'refresh',
      },
      {
        text: 'Экспорт в excel',
        extend: 'excelHtml5',
        exportOptions: {
          columns: ':visible',
        },
      },
    ],
    onAddRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlAddGym,
        method: 'POST',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
    onDeleteRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlDeleteGym.replace('id', rowdata.id),
        method: 'DELETE',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
    onEditRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlEditGym.replace('id', rowdata.id),
        method: 'POST',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
  })

  gymPicturesDataTable = gymPicturesTable.DataTable({
    scrollX: true,
    language: {
      url: $('.table-responsive').data('translationUrl'),
    },
    sPaginationType: 'full_numbers',
    data: [],
    columns: gymPicturesColumnDefs,
    dom: 'Bfrtip',
    select: 'single',
    responsive: false,
    altEditor: true,
    buttons: [
      {
        text: 'Добавить',
        name: 'add',
      },
      {
        extend: 'selected',
        text: 'Удалить',
        name: 'delete',
      },
    ],
    onAddRow: function (datatable, rowdata, success, error) {
      const formData = new FormData()
      formData.append('file', rowdata['url'][0], rowdata['url'][0].name)
      $.ajax(urlAddPicture.replace('id', gymPicturesTable.data('gymId')), {
        method: 'post',
        data: formData,
        processData: false,
        contentType: false,
        success: success,
        error: error,
      })
      gymsDataTable.ajax.reload(null, false)
    },
    onDeleteRow: function (datatable, rowdata, success, error) {
      $.ajax({
        url: urlDeletePicture.replace('id', rowdata.id),
        method: 'DELETE',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
        processData: false,
      })
      gymsDataTable.ajax.reload(null, false)
    },
  })

  gymsDataTable.on('select', function (e, dt, type, indexes) {
    if (type === 'row') {
      noGymSelectedInfo.hide()
      gymPicturesCardBody.removeClass('border-left-info')
      gymPicturesTableContainer.show()

      var data = gymsDataTable.row(indexes[0]).data()
      gymPicturesTable.data('gymId', data.id)

      gymPicturesDataTable.clear()
      gymPicturesDataTable.rows.add(data.photos)
      gymPicturesDataTable.draw()
    }
  })

  gymsDataTable.on('deselect', function (e, dt, type, indexes) {
    if (type === 'row') {
      gymPicturesTableContainer.hide()
      gymPicturesCardBody.addClass('border-left-info')
      noGymSelectedInfo.show()
    }
  })
})
