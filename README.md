# SDM - Stadtrat Dresden Metascrape

Damit können alle Dokumente eines Stadrats, der auch auf auf AllRIS/Sessionnet basiert, herunter geladen werden.

Aus den Downloadinformationen und dem Versuch die PDFs auszulesen werden weitere Informationen neben den Dokumenten abgelegt. 

Um PDF Dokumente auszulesen wird das Programm pdftohtml (für Linux and Windows erhältlich) benötigt.

Dieses PRojekt soll nur der Anfang sein um alle Dukumente und Informationen aus dem Stadtrat als OpenData vorliegen zu haben. Später wird versucht mit dem Projekt weiter und tiefer in die Erkennung der Inhalte der PDFs hineinzugehen.

Auf http://www.boogiedev.net/staDDratmeta wird dieses Programm regelmässig ausgeführt und dessen Resultat zusammengefasst als *.zip zum Download angeboten. Dieses enthält nicht die Dokumente, sondern nur dessen extrahierten MetaINformationen und eine leicht weiter zu verwendende CSV Datei mit allen Daten.  Um die orginalen PDFs (~24k stk, 17Gb) selber zu haben, muss SDM heruntergeladen und ausgeführt werden.


# Startscripte
start_update.sh
	Startet den Download aller DoKumente aus dem Ratsinformationssystem. Sind Dateien bereits schon vorhanden 		sucht es nur nach neueren Dateien

start_diff.sh
	Überprüft, ob sich die Dokumente auf dem Server ggü den heruntergeladenen verändert haben und legt 			verschiedene Versionen ab.


Erklärung der Scripte:
<ol>
	<li><strong> scrape.php </strong>
Läd alle im Stadtrat verfügbaren Dokumente auf einmal herunter. Im Script sind auch weitere Funktionen, mit denen später auch weitere Dokumente nachgeladen werden können. Dieses Script legt mehr als 24k PDFs und zu jeder Datei noch eine "*.scrapeinfo" Datei auf der Festplatte ab, in der Informationen zum download enthalten sind und die später als container für weitere Metadaten dienen und als Vergleichswerte für spätere Veränderungsanalysen dienen.</li>
	<li><strong>pdfmetascapeinfo.php
</strong>Wandelt alle PDFs mittels des Programmes <a href="http://pdftohtml.sourceforge.net/" target="_blank">pdftohtml</a> (muss auf Linux installiert sein und auf Windows als exe neben dem script liegen), wertet den Inhalt aus und schreibt alle erkannten Informationen Zeilenweise mit einem Bezeichner in die dazugehörige *.scrapeinfo.</li>
	<li><strong>scrapeinfo2csv.php
</strong>Noch ein kleiner tweak. Damit nicht immer zig tausende Dateien durchsucht werden müssen, speichert dieses Script den Inhalt aller *.scapeinfo in eine einzige csv Datei, die dann leicht in Exel, Calc oder andere Programme geladen werden kann um auch selbst schnell und einfach ein bisschen herum zu forschen. Diese Datei ist im Paket unter: http://www.boogiedev.net/staDDratmeta auch immer enthalten.</li>
	<li><strong>htmlgrabber.php
</strong>Bibiliothek mit Befehlen zum html parsen/auslesen.</li>
</ol>
