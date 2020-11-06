/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars

function ProgramReport (programId, loginUrl, reportSentText, errorText,
  reportButtonText, cancelText, reportDialogTitle,
  sexualLabel, violenceLabel, hateLabel, improperLabel, drugsLabel, copycatLabel, otherLabel,
  statusCodeOk, loggedIn) {
  const SEXUAL_VALUE = 'Sexual content'
  const VIOLENCE_VALUE = 'Graphic violence'
  const HATE_VALUE = 'Hateful or abusive content'
  const IMPROPER_VALUE = 'Improper content rating'
  const DRUGS_VALUE = 'Illegal prescription or other drug'
  const COPYCAT_VALUE = 'Copycat or impersonation'
  const OTHER_VALUE = 'Other objection'
  const CHECKED = 'checked'
  const SESSION_OLD_REPORT_CATEGORY = 'oldReportCategory' + programId

  const reportUrl = 'http://localhost/api/project/' + programId + '/report'

  $('#top-app-bar__btn-report-project').click(function () {
    if (!loggedIn) {
      window.location.href = loginUrl
      return
    }

    let oldReportCategory = sessionStorage.getItem(SESSION_OLD_REPORT_CATEGORY)
    if (oldReportCategory === null) {
      oldReportCategory = ''
    }
    reportProgramDialog(false, oldReportCategory)
  })

  function reportProgramDialog (error = false, oldCategory = '') {
    Swal.fire({
      title: reportDialogTitle,
      html: getReportDialogHtml(error, oldCategory),
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: reportButtonText,
      cancelButtonText: cancelText,
      preConfirm: function () {
        return new Promise(function (resolve) {
          resolve([
            $('input[name=report-category]:checked').val()
          ])
        })
      }
    }).then((result) => {
      if (result.value) {
        handleSubmitProgramReport(result.value)
      }
    })
  }

  $(document).on('change', 'input[name=report-category]', function () {
    sessionStorage.setItem(SESSION_OLD_REPORT_CATEGORY, $('input[name=report-category]:checked').val())
  })

  function handleSubmitProgramReport (result) {
    let category = result[0]

    if (category === null) {
      category = ''
      reportProgramDialog(true, category)
    } else {
      reportProgram(category)
    }
  }

  function reportProgram (category) {
    $.ajax({
      url: reportUrl,
      type: 'POST',
      data: JSON.stringify({
        category: category
      }),
      dataType: 'json',
      contentType: 'application/json; charset=UTF-8',
      beforeSend: function (xhr) {   //Include the bearer token in header
        xhr.setRequestHeader('Authorization', 'Bearer ' +
          'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MDQ1OTAwMjEsImV4cCI6MTYwNDU5MzYyMSwicm9sZXMiOlsiUk9MRV9TVVBFUl9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImNhdHJvd2ViIn0.hBAcX5x4gYRdE1-89DiHUV8UwSWMEhmGt2Es_NcKxtpU2aAriVXS6fHZct5re6eKLsw_V68GlZCFUbR7PyaKiHDoylCMXt8wmV3GA5IPE-N9_OJ_R8-w_u7PNpG4Dxfzd5iM9yz174LJySPGaAef8AcV6shaCs2vlNYgITN3VMBcLsMqQ2qVEAhZcFjgUgKGlwiXDkghMdqCNH-gtOHrLzhIMHVJwU1b79eH5KXnMZI1rvRLLPlbJxGJkcSL9lWZmFW-FZM8g3rwUIADvVXR0G-mRx1dkkFIwxQ-e039XlQNLQ18m3DfdaBfe_bEybOjQ0w4JANjjOBeyfzFAJNxMp59z2pTHtuJ2FGZQxzE-NcG4aC9_JiZFP3tUItogasc0qI3MqYmKSxkHDeP5G7Z19CHygQK4a2_mb6C9rn--hd-t_svabJImFzAHu5Cbm6ZD2JLqg7BFtYP7u1Dm8KMPA1LVvnSHOsD5FWDvuWweV5hG2OKgvPFoNwUDECsY1ukgesf7EcFcgPdPARlp-hj6FphSlJpHhPar7bcZbvBX1gYfyNZ_j2ppgGQhgk0Ya08p_f7TWrn9qCqDl_wjkhiQwnogUt7_0mvKnPJxyj-8pO2whWYlAZli2S9XwjbzZPDizBy0CLuT4hbvoehponRLg87B4YSWyCICiobHBBVI_A'
        )
      },
      success: function(response) {
        Swal.fire({
          text: reportSentText,
          icon: 'success',
          confirmButtonClass: 'btn btn-success'
        }).then(function () {
          window.location.href = '/'
        })
        console.log(response)
      },
      error: function (data, textStatus, xhr){
        Swal.fire({
          title: errorText,
          text: data.status + data.statusText,
          icon: 'error'
        })
        console.log(data, textStatus, xhr)
      },
      fail: function (data, textStatus, xhr)
      {
        Swal.fire({
          title: errorText,
          text: data.status + data.statusText,
          icon: 'error'
        })
        console.log(data, textStatus, xhr)
      }
    })
  }

  function getReportDialogHtml (error, oldCategory) {
    let checkedSexual = ''
    let checkedViolence = ''
    let checkedHate = ''
    let checkedImproper = ''
    let checkedDrugs = ''
    let checkedCopycat = ''
    let checkedOther = ''

    switch (oldCategory) {
      case SEXUAL_VALUE :
        checkedSexual = CHECKED
        break
      case VIOLENCE_VALUE:
        checkedViolence = CHECKED
        break
      case HATE_VALUE:
        checkedHate = CHECKED
        break
      case IMPROPER_VALUE:
        checkedImproper = CHECKED
        break
      case DRUGS_VALUE:
        checkedDrugs = CHECKED
        break
      case COPYCAT_VALUE:
        checkedCopycat = CHECKED
        break
      case OTHER_VALUE:
        checkedOther = CHECKED
        break
      default:
        checkedSexual = CHECKED
        break
    }

    return '<div class="text-left">' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-sexual" name="report-category" value="' +
      SEXUAL_VALUE + '" ' + checkedSexual + '>' +
      '<label for="report-sexual">' + sexualLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-violence" name="report-category" value="' +
      VIOLENCE_VALUE + '" ' + checkedViolence + '>' +
      '<label for="report-violence">' + violenceLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-hate" name="report-category" value="' +
      HATE_VALUE + '" ' + checkedHate + '>' +
      '<label for="report-hate">' + hateLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-improper" name="report-category" value="' +
      IMPROPER_VALUE + '" ' + checkedImproper + '>' +
      '<label for="report-improper">' + improperLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-drugs" name="report-category" value="' +
      DRUGS_VALUE + '" ' + checkedDrugs + '>' +
      '<label for="report-drugs">' + drugsLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-copycat" name="report-category" value="' +
      COPYCAT_VALUE + '" ' + checkedCopycat + '>' +
      '<label for="report-copycat">' + copycatLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-other" name="report-category" value="' +
      OTHER_VALUE + '" ' + checkedOther + '>' +
      '<label for="report-other">' + otherLabel + '</label>' +
      '</div>'
  }
}
