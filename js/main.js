let disruptions = null;

document.addEventListener('DOMContentLoaded', async function () {
  const response = await fetch(urlJson);
  disruptions = await response.json();

  if(document.querySelector('.ligne .e')) {
      window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
  }
  document.querySelector('#lignes').addEventListener('mouseover', function(e) {
      if(e.target.title) {
          replaceMessage(e.target);
      }
  })
  document.querySelector('#lignes').addEventListener('mouseout', function(e) {
      if(e.target.title) {
          e.target.title = e.target.dataset.title
          delete e.target.dataset.title
      }
  })
  document.querySelector('#lignes').addEventListener('click', function(e) {
      if(e.target.title) {
          replaceMessage(e.target);

          modal.innerText = e.target.title
          modal.showModal()
      }
  })
  const modal = document.getElementById('tooltipModal')
  modal.addEventListener('click', function(event) {
      modal.close();
  });
  const modalHelp = document.getElementById('helpModal')
  modalHelp.addEventListener('click', function(event) {
      modalHelp.close();
  });
  modal.addEventListener('close', function(event) {
      const item = document.querySelector('[data-title]')
      if(item && item.title) {
          item.title = item.dataset.title
          delete item.dataset.title
      }
  })
})

function replaceMessage(item) {
    item.dataset.title = item.title;
    for(let disruptionId of item.title.split("\n")) {
        if(disruptionId.match(/^%/)) {
            disruptionId=disruptionId.replace(/%/g, '')
            if(disruptionId && disruptions[disruptionId]) {
                item.title = item.title.replace('%'+disruptionId+'%', disruptions[disruptionId])
            }
        }
    }
    item.title = item.title.replace(/\n\n\n/g, '')
}
