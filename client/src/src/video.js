(function ($) {
  var currentEditorID, currentPreview, currentEditorInput, currentDetectorInput

  var allEditors = []

  function afterEditorInit() {
    initPreview()
  }

  function initPreview(editor) {
    if (!editor) {
      const editor = getEditor()
    }

    currentPreview = jQuery(
      editor.element.closest('[data-goldfinch-video-field]').querySelector('[data-goldfinch-video-preview]'),
    )

    if (editor) {
      const data = editor.getValue()
      if (data.host && data.id) {
        currentPreview.html(
          '<a href="'
          + getEmbededUrl(data)
          + '" class="ss-ui-dialog-link"><img src="'
          + getPreviewImage(data)
          + '" alt="Preview"></a>',
        )
        currentPreview.addClass('goldfinchvideo__preview--display')
      }
      else {
        currentPreview.removeClass('goldfinchvideo__preview--display')
      }
    }
  }

  function getPreviewImage(data) {
    let url
    if (data.host == 'youtube') {
      url = 'https://img.youtube.com/vi/' + data.id + '/mqdefault.jpg'
    }
    else if (data.host == 'vimeo') {
      url = 'https://vumbnail.com/' + data.id + '_large.jpg'
    }
    return url
  }

  function getEmbededUrl(data) {
    let url
    if (data.host == 'youtube') {
      url = 'https://www.youtube.com/embed/' + data.id
    }
    else if (data.host == 'vimeo') {
      url = 'https://player.vimeo.com/video/' + data.id
    }
    return url
  }

  function detectHostPlatform(link, el) {
    var host, videoID

    if (link.search('youtube.com/') >= 0 || link.search('youtu.be/') >= 0) {
      host = 'youtube'
      videoID = getYoutubeID(link)
    }
    else if (link.search('vimeo.com/') >= 0) {
      host = 'vimeo'
      videoID = getVimeoID(link)
    }

    if (host && videoID) {
      const editor = getEditor(el.getAttribute('data-field-id')) // currentEditor ? currentEditor : (window.jsoneditor ? window.jsoneditor[id] : null)

      if (editor) {
        var currentVal = editor.getValue()

        currentVal['host'] = host
        currentVal['id'] = videoID
        editor.setValue(currentVal)

        initPreview(editor)

        // currentDetectorInput.val('')
      }
    }
  }

  function getYoutubeID(url) {
    url = url.split(/(vi\/|v%3D|v=|\/v\/|youtu\.be\/|\/embed\/)/)
    return undefined !== url[2] ? url[2].split(/[^0-9a-z_\-]/i)[0] : url[0]
  }

  function getVimeoID(url) {
    let regEx = /(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/
    let match = url.match(regEx)
    if (match && match.length == 7) {
      return match[6]
    }
  }

  function editorInit(editor) {
    afterEditorInit()
  }

  function getEditor(id) {
    return window.jsoneditor ? window.jsoneditor[id] : null
  }

  function initAllPreviews() {
    allEditors.map((i, k) => {
      if (!i.previewInit) {
        initPreview(getEditor(i.id))
        allEditors[k].previewInit = true
      }
    })
  }

  $(document).ready(() => {
    let vc = 0
    var vint = setInterval(() => {
      vc++
      const editor = getEditor()
      if (editor) {
        clearInterval(vint)
        editorInit(editor)
      }
      if (vc > 5) {
        clearInterval(vint)
      }
    }, 500)
  })

  $('.cms-edit-form').entwine({
    onmatch(e) {
      this._super(e)
    },
    onunmatch(e) {
      this._super(e)
    },
    onaftersubmitform(event, data) {
      currentPreview = $(this).find('[data-goldfinch-video-preview]')
      editorInit(getEditor())
      initPreview()
    },
  })

  $.entwine('ss', ($) => {
    $('[data-goldfinch-video-field]').entwine({
      onmatch() {
        const editorEl = $(this).find('.json-editor')
        currentEditorInput = editorEl.next()

        currentDetectorInput = currentEditorInput
          .closest('[data-goldfinch-video-field]')
          .find('[data-goldfinch-video-link-detector]')
        currentPreview = currentEditorInput
          .closest('[data-goldfinch-video-field]')
          .find('[data-goldfinch-video-preview]')

        currentEditorID = currentEditorInput.attr('id')
        currentDetectorInput[0].setAttribute('data-field-id', currentEditorID)

        allEditors.push({ id: currentEditorID, previewInit: false })

        setTimeout(() => initAllPreviews(), 500)

        currentDetectorInput.on('keyup', (e) => {
          detectHostPlatform(e.target.value, e.target)
        })
      },
    })
  })
})(jQuery)
