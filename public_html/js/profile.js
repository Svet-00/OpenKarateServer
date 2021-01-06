$(function () {
  'use strict'

  // * dom elements
  const $imageEditModal = $('#imageEditModal')
  // avatar actions
  const $changeAvatarViewBtn = $('#changeAvatarView')
  const $deleteAvatarBtn = $('#deleteAvatar')
  // form file input
  const $avatarInput = $('#newProfileImg')
  // images which are edited with cropper in modal
  const $imageToEditSquare = $('#imageToEditSquare')
  const $imageToEditWide = $('#imageToEditWide')
  // modal buttons
  const $modalDismissBtns = $('.modalDismiss')
  const $modalSaveBtn = $('#modalSave')

  //* cropper initialization
  Cropper.setDefaults({
    viewMode: 2,
    movable: false,
    rotatable: false,
    scalable: false,
    zoomable: false,
  })
  const cropperSquare = new Cropper($imageToEditSquare[0], {
    aspectRatio: 1,
  })
  const cropperWide = new Cropper($imageToEditWide[0], {
    aspectRatio: 16 / 10,
  })

  // * update avatar
  $changeAvatarViewBtn.on('click', (e) => {
    $imageEditModal.modal('show')
  })

  $avatarInput.on('change', (e) => {
    var file = $avatarInput.prop('files')[0]
    $imageEditModal.data('originalImageSrc', URL.createObjectURL(file))
    $imageEditModal.modal('show')
  })

  $modalDismissBtns.on('click', (e) => {
    $avatarInput.val('')
  })

  $modalSaveBtn.on('click', async (e) => {
    // TODO: проверить, что размер загружаемого изображения не превышает 10мб
    // todo (ограничение сервера)
    const formData = new FormData()

    // can store normal url or blob object url
    var originalImg = $imageEditModal.data('originalImageSrc')

    originalImg = await fetch(originalImg).then((r) => r.blob())
    formData.append('originalImg', originalImg)

    formData.append('token', $modalSaveBtn.data('token'))

    cropperSquare.getCroppedCanvas().toBlob((squareImgBlob) => {
      formData.append('squareImg', squareImgBlob)
      cropperWide.getCroppedCanvas().toBlob((wideImgBlob) => {
        formData.append('wideImg', wideImgBlob)
        console.log(formData.values())
        $.ajax($modalSaveBtn.data('submitUrl'), {
          method: 'post',
          data: formData,
          processData: false,
          contentType: false,
          success: (data, textStatus, jqXHR) => {
            $('.avatar').attr('src', URL.createObjectURL(squareImgBlob))
            $imageEditModal.data(
              'originalImageSrc',
              URL.createObjectURL(originalImg)
            )
            $changeAvatarViewBtn.removeClass('disabled')
            $deleteAvatarBtn.removeClass('disabled')
          },
          error: () => {
            $('#avatarUploadErrorAlert').show()
          },
        })

        $modalDismissBtns.trigger('click')
      })
    })
  })

  $imageEditModal.on('shown.bs.modal', (e) => {
    const imgSrc = $imageEditModal.data('originalImageSrc')
    cropperSquare.replace(imgSrc, false)
    cropperWide.replace(imgSrc, false)
  })

  $('form[name="passwordForm"]').on('submit', function (e) {
    if (
      $('#passwordForm_plainPassword')[0].value !==
      $('#passwordForm_repeatPassword')[0].value
    ) {
      e.preventDefault()
      $('#passwordsNotEqualError').show()
    }
  })
  $('#passwordForm_plainPassword').on('focus', function (e) {
    $('#passwordsNotEqualError').hide()
  })
  $('#passwordForm_repeatPassword').on('focus', function (e) {
    $('#passwordsNotEqualError').hide()
  })
})
