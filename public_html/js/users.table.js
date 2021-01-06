$(function () {
  const levelOptions = {
    0: '-',
    1: '11 Кю',
    2: '10 Кю',
    3: '9 Кю',
    4: '8 Кю',
    5: '7 Кю',
    6: '6 Кю',
    7: '5 Кю',
    8: '4 Кю',
    9: '3 Кю',
    10: '2 Кю',
    11: '1 Кю',
    12: '1 Дан',
    13: '2 Дан',
    14: '3 Дан',
    15: '4 Дан',
    16: '5 Дан',
    17: '6 Дан',
    18: '7 Дан',
    19: '8 Дан',
    20: '9 Дан',
    21: '10 Дан',
  }
  const columnDefs = [
    {
      data: 'id',
      title: 'Уникальный идентификатор',
      visible: false,
      searchable: false,
      type: 'hidden',
    },
    {
      data: 'email',
      title: 'Email',
      type: 'hidden',
    },
    {
      data: 'surname',
      title: 'Фамилия',
      type: 'hidden',
    },
    {
      data: 'name',
      title: 'Имя',
      type: 'hidden',
    },
    {
      data: 'patronymic',
      title: 'Отчество',
      type: 'hidden',
    },
    {
      data: 'birthday',
      title: 'Дата рождения',
      type: 'hidden',
      render: function (data, type, _row) {
        if (type === 'sort' || type === 'type') return Date.parse(data)
        var birthdayDate = new Date(data)
        return (
          birthdayDate.toLocaleDateString('ru-Ru') +
          ' (' +
          (new Date().getFullYear() - birthdayDate.getFullYear()) +
          ' лет)'
        )
      },
    },
    {
      data: 'level',
      title: 'Степень мастерства',
      render: function (data, type, _row) {
        if (type === 'sort' || type === 'type') {
          return data
        }
        return levelOptions[data]
      },
      type: 'select',
      options: levelOptions,
      multiple: false,
      select2: { width: '100%', language: 'ru' },
    },
    {
      data: 'trainer',
      title: 'Тренер',
      type: 'checkbox',
      render: function (data, _type, _row) {
        return data ? 'Да' : 'Нет'
      },
    },
    {
      data: 'admin',
      title: 'Администратор',
      type: 'checkbox',
      render: function (data, _type, _row) {
        return data ? 'Да' : 'Нет'
      },
    },
  ]

  var usersDataTable
  var usersTable = $('#usersTable')
  var urlGet = usersTable.data('getUrl')
  var urlAction = usersTable.data('editUrl')

  usersDataTable = usersTable.DataTable({
    scrollX: '100%',
    language: {
      url: $('.table-responsive').data('translationUrl'),
      buttons: {
        pageLength: {
          _: 'Показать %d записей на странице',
          '-1': 'Показать всё',
        },
      },
    },
    sPaginationType: 'full_numbers',
    ajax: {
      url: urlGet,
      dataSrc: '',
    },
    columns: columnDefs,
    dom: 'Bfrtip',
    select: { style: 'single', items: 'row' },
    responsive: false,
    altEditor: true,
    buttons: [
      {
        extend: 'selected',
        text: 'Редактировать',
        name: 'edit',
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
      'pageLength',
    ],
    lengthMenu: [
      [10, 25, 50, -1],
      ['10', '25', '50', 'Показать всё'],
    ],
    onEditRow: function (_datatable, rowdata, success, error) {
      $.ajax({
        url: urlAction.replace('id', rowdata.id),
        method: 'POST',
        data: rowdata,
        success: success,
        error: error,
        dataType: 'json',
      })
    },
  })
})
