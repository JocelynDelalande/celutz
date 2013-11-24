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


Getting started : upload a panorama
-----------------------------------

1. Go to the index and click "Ajouter un panorama", let upload process
2. Just click on the image in the subsequent list, and wait for it to be
   processed (split into tiles) ;
3. Choose "accéder directement au panorama" to view it and use the red up-right box to enter
   coordinates and altitudes from where the photos have beenn taken. *bouclage*
   means that left and right borders are matching exactly (360° panorama).
4. Reference some known points by right clicking (see relevant section), the
   others will get interpolated and presented to you on the view.


Viewing reference points
------------------------

You'll see big colored bubbles :

* *Blue points* : reference points you referenced and pointed by hand
* *Red points*  : other panorama (click and fly to it !)
* *Green points* : reference points automatically placed (estimated according to
   the blue ones)


Setting up reference points
----------------------------

Reference points are known points you are likely to see from your panoramas,
they are a visual reference and a way to orientate your panoramas.

By default, there are no *ref_points*, you can create your own *ref_points* list
or use one of the provided lists.

To get started, copy one of the files from `ref_points` folder in the root
folder, remane it to `ref_points.local.php` and customize it.

*Hint: prefer to register the altitudes of the topmost point of a building: they
 are the easire parts to aim from other panoramas.*


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


"Show cap" view
---------------

In this view, you see a map with your current view point and a ray between it
and your target ref_point.

Options and layers selection can be set in the `+` menu. To move from/to the, click
on *the top-right button*.
