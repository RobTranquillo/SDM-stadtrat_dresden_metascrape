# SDM-stadtrat_dresden_metascrape
download all documents from Stradrat Dresden and extract structured and semantic data from these pdfs not just plain text

This is the scraper to get the files and the converter that uses pdftohtml on console (for Linux and Windows available).

This is just the beginning to analyze the converted html and get some more structured, semantic metadata out of the government data, that was caged into PDF before.

Look at http://www.boogiedev.net/staDDratmeta to get the newest compressed output of this process chain.



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
