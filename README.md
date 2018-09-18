# Willkommen bei LOOP

LOOP ist ein am Institut für Lerndienstleistungen (ILD) der Technischen Hochschule Lübeck entwickeltes Online-Autorensystem mit dem Du ohne besondere Programmierkenntnisse online verfügbare, multimedial und didaktisch aufbereitete Lerninhalte erstellen kannst. Das Akronym LOOP steht für Learning Object Online Platform. 

Die Entwicklung von LOOP 1.0 begann 2013. Inzwischen werden am ILD bereits über 600 Installationen in einer LOOP-Farm betrieben. Falls Du Dich für die “alte” Version von LOOP interessiert guckt doch mal auf [loop.oncampus.de](https://loop.oncampus.de) Dort kannst Du Dir ein LOOP ansehen, das gleichzeitig als Anleitung für LOOP-Autoren dient.

Zurzeit arbeiten wir mit Hochdruck an LOOP 2.0, einem kompletten Redesign. Unter [www.facebook.com/loopcommunityoncampus/](https://www.facebook.com/loopcommunityoncampus/) halten wir Dich auf dem Laufenden wie es mit der Entwicklung voran geht.



## Installation

Du möchtest gerne Dein eigenes LOOP hosten? 

LOOP besteht aus
- einigen Core-Änderungen: https://github.com/oncampus/mediawiki
- einer Extention https://github.com/oncampus/mediawiki-extensions-Loop
- und einem Skin https://github.com/oncampus/mediawiki-skins-Loop

Folgende Extensions sind für LOOP angepasst:
- WikiEditor: https://github.com/oncampus/mediawiki-extensions-WikiEditor

Du solltest allerdings bedenken, dass es mit einem Mediawiki nicht getan ist. Zu LOOP, wie es am ILD gehostet wird geöhort auch ein Tools-Server.
Auf diesen sind etliche Services ausgelagert. Neben Mathoid, Lilypond und ngSpice gehören dazu auch einige kommerzielle Tools wie der AH Formatter für die PDF Erzeugung oder AWS Polly für die Audioausgabe.
