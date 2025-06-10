document.addEventListener('DOMContentLoaded', async function () {
  if(document.querySelector('.ligne .e')) {
      window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
  }

  const modalHelp = document.getElementById('modalHelp')
  const modalList = document.getElementById('listModal')
  const modalListTab = document.querySelector('#listModal #tabLigne')

  function checkLocationHash() {
    if(document.location.hash == "#aide") {
        modalHelp.showModal();
    } else {
        modalHelp.close();
    }

    if(modalList && modalListTab && document.location.hash.indexOf("#incidents_") === 0) {
      document.querySelectorAll('.liste_ligne').forEach(function(item) {
        item.style.display = 'none';
      });
      document.querySelectorAll('#tabLigne a').forEach(function(item) {
        item.classList.remove('active');
      });
      const line = document.location.hash.split(":")[0].split("_")[1];
      if(document.querySelector('#liste_'+line)) {
        document.querySelector('#liste_'+line).style.display = 'block';
      }
      filtreListeDisruption(line);
      document.querySelector('#tabLigne a[href="'+document.location.hash.split(":")[0]+'"]').classList.add('active');
      if(!modalList.open) {
        modalList.showModal();
        modalList.blur();
        document.querySelector('#tabLigne a').blur();
        setTimeout(function() {  document.querySelector('#tabLigne a.active').scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); }, 500);
      }
      let disruptionId = null;
      if(document.location.hash.split(":")[1]) {
        disruptionId = document.location.hash.split(":")[1].split(";")[0];
      }
      if(disruptionId && document.getElementById('disruption_' + disruptionId)) {
        document.getElementById('disruption_' + disruptionId).scrollIntoView();
      } else {
        modalList.scrollTo(0,0);
      }
    } else if(modalList && modalListTab && document.location.hash == '#incidents') {
      document.querySelectorAll('.liste_ligne').forEach(function(item) {
        item.style.display = 'none';
      });
      document.querySelectorAll('#tabLigne a').forEach(function(item) {
        item.classList.remove('active');
      });
      if(document.querySelector('#liste_TOTAL')) {
        document.querySelector('#liste_TOTAL').style.display = 'block';
      }
      filtreListeDisruption(null);
      document.querySelector('#tabLigne a[href="'+document.location.hash+'"]').classList.add('active');
      if(!modalList.open) {
        modalList.showModal();
        modalList.blur();
        document.querySelector('#tabLigne a').blur();
      }
      modalList.scrollTo(0,0);
    } else if(modalList) {
      modalList.close();
    }
  }

  checkLocationHash();

  window.addEventListener('hashchange', function() {
    checkLocationHash();
  });

  document.querySelector('#lignes').addEventListener('click', function(e) {
      if((!e.target.closest('a') || !e.target.closest('a').href) && e.target.closest('.ligne')) {
          let hash = e.target.closest('.ligne').querySelector('.logo a').hash;
          if(e.target.dataset.id) {
              hash += ':' + e.target.dataset.id;
          }
          document.location.hash = hash;
      }
  })

  modalHelp.addEventListener('click', function(event) {
    if(event.target.nodeName != "A") {
      modalHelp.close();
    }
  });
  modalHelp.addEventListener("close", function(e) {
    history.replaceState(null, null, ' ');
  });
  if(modalList) {
    modalList.addEventListener('click', function(event) {
        if(event.target.classList.contains('ellips')) {
            let beforeheight = event.target.offsetHeight;
            event.target.classList.remove('ellips');
            if(beforeheight != event.target.offsetHeight) {
                return;
            }
        }

        if(event.target.nodeName != "A" && event.target.parentElement.nodeName != "A") {
            modalList.close();
        }
    });
    modalList.addEventListener("close", function(e) {
        filtreListeDisruption();
        this.querySelectorAll('.disruption ul  li p').forEach(function(item) {
            item.classList.add('ellips');
        });
        history.replaceState(null, null, ' ');
    });
  }
})

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
  if(document.querySelector('#listModal #title_disruptions_inprogress')) {
    let nbIncidentsInProgress = document.querySelectorAll('#listModal #disruptions_inprogress .disruption:not(.hide)').length;
    document.querySelector('#listModal #title_disruptions_inprogress span.badge').innerText = nbIncidentsInProgress+' incidents';
    document.querySelector('#listModal #title_disruptions_inprogress span.badge').classList.remove('hide')
    document.querySelector('#listModal #title_disruptions_inprogress').classList.remove('hide');
    document.querySelector('#listModal #sentence_nothing_disruptions').classList.add('hide');
    if(!nbIncidentsInProgress) {
      document.querySelector('#listModal #sentence_nothing_disruptions').classList.remove('hide');
    }
  }
  if(document.querySelector('#listModal #title_disruptions_finishes')) {
    let nbIncidentsFinish = document.querySelectorAll('#listModal #disruptions_finishes .disruption:not(.hide)').length;
    document.querySelector('#listModal #title_disruptions_finishes span.badge').classList.remove('hide')
    document.querySelector('#listModal #title_disruptions_finishes span.badge').innerText = nbIncidentsFinish+' incidents';
    document.querySelector('#listModal #title_disruptions_finishes').classList.remove('hide');
    document.querySelector('#listModal #sentence_nothing_disruptions_finish').classList.add('hide');
    if(!nbIncidentsFinish) {
      document.querySelector('#listModal #sentence_nothing_disruptions_finish').classList.remove('hide');
    }
  }
}
