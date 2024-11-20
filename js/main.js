let disruptions = null;

document.addEventListener('DOMContentLoaded', async function () {
  const response = await fetch(urlJson.replace('.json', '.json?'+Date.now()));
  disruptions = await response.json();

  if(document.querySelector('.ligne .e')) {
      window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
  }

  document.querySelector('#btn_help').addEventListener('click', function(e) {
      document.getElementById('helpModal').showModal();
      return false;
  });

  document.querySelector('#btn_list').addEventListener('click', function(e) {
      filtreListeDisruption();
      document.getElementById('listModal').showModal();
      return false;
  });

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
      if(e.target.closest('.ligne')) {
        filtreListeDisruption(e.target.closest('.ligne').dataset.id);
        document.getElementById('listModal').showModal();
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
  const modalList = document.getElementById('listModal')
  modalList.addEventListener('click', function(event) {
      modalList.close();
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
    item.title = item.title.replace("%no%", "\n\nLe service est terminé ou pas encore commencé");
    for(let disruptionId of item.title.split(";")) {
        if(disruptionId.match(/^%/)) {
            disruptionId=disruptionId.replace(/%/g, '')
            if(disruptionId && disruptions[disruptionId]) {
                item.title = item.title.replace(';%'+disruptionId+'%', "\n\n"+disruptions[disruptionId].replace(/[\n]+$/, ""))
            }
            if(disruptionId && disruptions[disruptionId] == null) {
                item.title = item.title.replace(';%'+disruptionId+'%', "")
            }
        }
    }
}

function filtreListeDisruption(ligneId = null) {
  document.querySelectorAll('#listModal .disruption').forEach(function(item) {
    if(ligneId) {
      item.classList.add('hide');
    } else {
      item.classList.remove('hide');
    }
  });
  if(ligneId) {
    document.querySelectorAll('#listModal .disruption[data-line="'+ligneId+'"]').forEach(function(item) {
      item.classList.remove('hide');
    });
  }
}
