# DeLorean-Wiederherstellung

![DeLorean](https://raw.githubusercontent.com/Krassmus/DeLorean/master/assets/TeamTimeCar.com-BTTF_DeLorean_Time_Machine-OtoGodfrey.com-JMortonPhoto.com-07.jpg)

Mit diesem Plugin werden alle Objekte in Stud.IP, die verändert oder gelöscht werden, gespeichert. Root kann die Veränderungen einsehen, nachvollziehen und wieder rückgängig machen. Die DeLorean-Wiederherstellung ist somit eine Zeitmaschine, mit der man jeden Zeitpunkt von Stud.IP einsehen und partiell wieder zum Leben erwecken kann.

## Technisches

Technisch werden alle Objekte, die von der Klasse SimpleORMap abgeleitet sind, in einer extra Tabelle gespeichert.

Ab der Stud.IP-Version 4.0 funktioniert das Plugin frei Haus. In Stud.IP-Versionen vor 4.0 braucht man zwei kleine Kernänderungen in der Form von Notifications, die man in die Klasse SimpleORMap einbauen muss, damit dieses Plugin funktionieren kann.

## Features

* Man kann sich zu jedem gespeicherten SORM-Objekt die Inhalte anzeigen lassen. Änderungen gegenüber der Vorgängerversion werden hervorgehoben.
* Man kann Änderungen wieder rückgängig machen also auf die letzte Version zurück gehen.
* Dateien werden mitgespeichert und können ebenfalls wieder hergestellt werden.
* Datenschutz: Da eine Tabelle mit allen Nutzeraktivitäten aller Zeiten datenschutztechnisch bedenklich ist, werden die user_ids nach einem Zeitraum von einem Monat anonymisiert. Per Konfiguration kann dieser Wert auch beliebig nach oben oder auf 0 Sekunden gesetzt werden, um maximalen Datenschutz zu gewährleisten.
* Besondere Views: Suche nach Inhalten, zeige alle zeitgleichen Änderungen an, zeige alle Änderungen von SORM-Klasse X an, zeige Historie nur dieses einen Objektes.

## Lizenz

Das Plugin steht unter "GPL 2 or later" und der Mozilla Public License Version 2. 
Das obige Bild eines DeLorean DMC-12 steht unter Creative Commons Attribution-Share Alike 4.0 und stammt vom Wikipedia-User Terabass, siehe hier http://en.wikipedia.org/wiki/File:TeamTimeCar.com-BTTF_DeLorean_Time_Machine-OtoGodfrey.com-JMortonPhoto.com-07.jpg

Der Name DeLorean hat kein aktives Trademark und kann frei verwendet werden.

