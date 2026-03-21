<span class="modalClose"></span>
<h2>Aide et informations</h2>
<p>RATPstatus.fr est une page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France.</p>
<p>L'état du trafic est récupéré toutes les 2 minutes à partir du 23 avril 2024.</p>
<p>Chaque bloc répresente une durée de 2 minutes, les couleurs ont la signification suivante :<br /><br />
    <span class="ok"></span> Rien à signaler<br />
    <span class="pb"></span> Perturbation<br />
    <span class="bq"></span> Blocage / Interruption<br />
    <span class="tx"></span> Travaux<br />
    <span class="no"></span> Service terminé ou non commencé
<br />
</p>
<p>Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a>.</p>
<?php if(isset($day) && $day->getLastFile()): ?>
<p>La dernière récupération pour ce jour date du <a href="https://github.com/wincelau/ratpstatus/blob/main/<?php echo str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $day->getLastFile()->getFilePath()) ?>"><?php echo $day->getLastFile()->getDate()->format('d/m/Y H:i:s') ?></a>.</p>
<?php endif; ?>

<p>Chaque nuit l'historique des informations présentées sur cette page sont <a href="/export/">exportées au format CSV</a> (librement exploitables dans le respect de la licence <a href="https://opendatacommons.org/licenses/odbl/summary/">ODbL</a>).

<p>Le projet initié par <a href="https://piaille.fr/@winy">winy</a> est publié sous licence libre AGPL-3.0 : <a href="https://github.com/wincelau/ratpstatus">https://github.com/wincelau/ratpstatus</a>.</p>
<p>Ce site n'est pas un site officiel de la <a href="https://www.ratp.fr/">RATP</a>.</p>

<p><button id="install">📱 Installer l'application</button></p>
