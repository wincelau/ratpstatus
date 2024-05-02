let disruptions = null;

document.addEventListener('DOMContentLoaded', async function () {
  const response = await fetch(urlJson.replace('.json', '.json?'+Date.now()));
  disruptions = await response.json();

  if(document.querySelector('.ligne .e')) {
      window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
  }
  document.querySelector('#lignes').addEventListener('mouseover', async function(e) {
      if(e.target.title) {
          await replaceMessage(e.target);
      }
  })
  document.querySelector('#lignes').addEventListener('mouseout', function(e) {
      if(e.target.title) {
          e.target.title = e.target.dataset.title
          delete e.target.dataset.title
      }
  })
  document.querySelector('#lignes').addEventListener('click', async function(e) {
      if(e.target.title) {
          await replaceMessage(e.target);

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
    item.title = item.title.replace("%ok%", "\n\nRien à signaler");
    for(let disruptionId of item.title.split(";")) {
        if(disruptionId.match(/^%/)) {
            disruptionId=disruptionId.replace(/%/g, '')
            if(disruptionId && disruptions[disruptionId]) {
                item.title = item.title.replace(';%'+disruptionId+'%', "\n\n"+disruptions[disruptionId])
            }
            if(disruptionId && disruptions[disruptionId] == null) {
                item.title = item.title.replace(';%'+disruptionId+'%', "")
            }
        }
    }
}
