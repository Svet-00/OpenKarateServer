'use_strict'
window.addEventListener('load', function (_, _1) {
  const eventFormLinksContainer = document.getElementById('eventForm_links')
  const addLinkToEventBtn = document.getElementById('addLinkToEventBtn')
  const eventLinkDeleteBtns = document.getElementsByClassName(
    'eventLinkDeleteBtn'
  )
  const deleteLinkFromForm = function (_) {
    this.parentElement.parentElement.remove()
  }

  Array.from(eventLinkDeleteBtns).forEach(function (btn) {
    btn.addEventListener('click', deleteLinkFromForm)
  })

  addLinkToEventBtn.addEventListener('click', function (e) {
    const template = `
    <div class="card mb-2 text-sm">
      <div class="card-header d-flex flex-row-reverse">
        <a href="#"
          class="btn btn-secondary btn-sm btn-circle eventLinkDeleteBtn">
          <span class="material-icons-outlined">delete</span>
        </a>
      </div>
      <div class="card-body">
        {{ placeholder }}
      </div>
    </div>
    `
    const linksCount = eventFormLinksContainer.children.length
    const prototype = addLinkToEventBtn.dataset['prototype'].replace(
      /__name__/g,
      linksCount + 1
    )
    const html = template.replace('{{ placeholder }}', prototype)
    const linkSubform = document.createElement('div')
    linkSubform.innerHTML = html
    linkSubform
      .getElementsByClassName('eventLinkDeleteBtn')
      .item(0)
      .addEventListener('click', deleteLinkFromForm)

    eventFormLinksContainer.append(linkSubform)
  })

  $('#deleteEventModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget)
    const removeUrl = button.data('eventRemoveUrl')
    const eventText = button.data('eventTitle')
    const modal = $(this)
    modal
      .find('.modal-body')
      .text('Вы действительно хотите удалить событие "' + eventText + '"?')
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
