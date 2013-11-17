Panorama-ttnn
=============

*Visualize a collection of panoramic photos.*

**Panorama-ttnn** allows you to upload/share/visualize panoramic photos. It has been
created for evaluating lines-of-sight for radio networks, like the one of
[tetaneutral.net](http://tetaneutral.net).

Features
--------

* **Upload** panoramas to web server (i.e: made with [Hugin](hugin.sf.net));
* **visualize**, pan and zoom panoramas, as if you were on site;
* **georeference** panoramas : set GPS coordinates and elevation by hand and set
  orientation by pointing at known points;
* **visualize a point** by lat/lon/elevation on your panorama ;
* **see other panoramas** locations to evaluate the lines-of-sight;
* **view on map** for a given LOS between two points/panoramas.


Known Limitations
-----------------

* The reference points are hardcoded in ref_points.php

Panorama view
--------------

This is the main view, you can pan and scroll a panorama.


From this view you can use the *Reference points menu*. The reference points
menu allows you to set the orientation of your panorama by pointing at a known
location you visualize on the panorama.




### Mouse interaction for panorama view ###

* *drag image*  to move
* *right-click* to pop the Reference points menu


### Keyboard shortcuts for panorama view ###

* `Pgup`/`Pgdown`: zoom in/out
* `←`/`↑`/`↓`/`→`: pan the image
* `Home`/`End`: turn backwards (180°)
