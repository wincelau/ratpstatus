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
      modalList.showModal();
      modalList.scrollTo(0,0);
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
        let ligne = e.target.closest('.ligne');
        let img = ligne.querySelector('.logo img');
        let modalTitle = document.querySelector('#listModal #listModal_title_line');
        filtreListeDisruption(ligne.dataset.id);
        modalTitle.innerHTML = '<img src="'+img.src+'" style="height: 20px;" /> '+img.alt;
        modalList.showModal();
        modalList.scrollTo(0,0);
        modalList.blur();
      }
  })
  const modalHelp = document.getElementById('helpModal')
  modalHelp.addEventListener('click', function(event) {
    if(event.target.nodeName != "A") {
      modalHelp.close();
    }
  });
  const modalList = document.getElementById('listModal')
  modalList.addEventListener('click', function(event) {
      if(event.target.classList.contains('ellips')) {
          let beforeheight = event.target.offsetHeight;
          event.target.classList.remove('ellips');
          if(beforeheight != event.target.offsetHeight) {
              return;
          }
      }

      if(event.target.nodeName != "A") {
          modalList.close();
      }
  });

  modalList.addEventListener("close", function(e) {
      filtreListeDisruption();
      this.querySelectorAll('.disruption ul  li p').forEach(function(item) {
          item.classList.add('ellips');
      });
  });
})

function replaceMessage(item) {
    item.dataset.title = item.title;
    item.title = item.title.replace("%ok%", "\n\nRien à signaler");
    item.title = item.title.replace("%no%", "\n\nLe service est terminé ou pas encore commencé");
    let disruptionsMessages = [];
    for(let disruptionId of item.title.split(";")) {
        if(disruptionId.match(/^%/)) {
            disruptionId=disruptionId.replace(/%/g, '')
            if(!disruptionsMessages.includes(disruptions[disruptionId]) && disruptionId && disruptions[disruptionId]) {
                item.title = item.title.replace(';%'+disruptionId+'%', "\n\n"+disruptions[disruptionId].replace(/[\n]+$/, ""))
                disruptionsMessages.push(disruptions[disruptionId])
            } else if(disruptionId) {
                item.title = item.title.replace(';%'+disruptionId+'%', "")
            }
        }
    }
}

function filtreListeDisruption(ligneId = null) {
  document.querySelector('#listModal #listModal_title_line').classList.toggle('hide', !ligneId);
  document.querySelector('#listModal #listModal_title_all').classList.toggle('hide', ligneId);

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
  if(document.querySelector('#listModal #title_disruptions_inprogress')) {
    let nbIncidentsInProgress = document.querySelectorAll('#listModal #title_disruptions_inprogress .disruption:not(.hide)').length;
    document.querySelector('#listModal #title_disruptions_inprogress span.badge').innerText = nbIncidentsInProgress+' incidents';
    document.querySelector('#listModal #title_disruptions_inprogress').classList.remove('hide');
    if(!nbIncidentsInProgress) {
      document.querySelector('#listModal #title_disruptions_inprogress').classList.add('hide');
    }
  }
  if(document.querySelector('#listModal #title_disruptions_finishes')) {
    let nbIncidentsFinish = document.querySelectorAll('#listModal #disruptions_finishes .disruption:not(.hide)').length;
    document.querySelector('#listModal #title_disruptions_finishes span.badge').innerText = nbIncidentsFinish+' incidents';
    document.querySelector('#listModal #title_disruptions_finishes').classList.remove('hide');
    if(!nbIncidentsFinish) {
      document.querySelector('#listModal #title_disruptions_finishes').classList.add('hide');
    }
  }
  document.querySelector('#listModal #sentence_nothing_disruptions').classList.add('hide');
  if(!document.querySelectorAll('#listModal .disruption:not(.hide)').length) {
    document.querySelector('#listModal #sentence_nothing_disruptions').classList.remove('hide');
  }
}
