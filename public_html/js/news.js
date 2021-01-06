$(function () {
  'use_strict'
  $('#deletePostModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget)
    const removeUrl = button.data('postRemoveUrl')
    const postText = button.data('postText')
    const modal = $(this)
    modal
      .find('.modal-body')
      .text(
        'Вы действительно хотите удалить пост с текстом "' + postText + '"?'
      )
    modal.find('.btn-danger').on('click', function (event) {
      $.ajax({
        url: removeUrl,
        method: 'DELETE',
        complete: function (jqXHR, textStatus) {
          location.reload()
        },
      })
    })
  })
})
