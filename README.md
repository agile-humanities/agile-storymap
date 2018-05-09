Storymap (module for Omeka S)
=============================


[Storymap] is a module for [Omeka S] that integrates the
the online [Knightlab storymap] to create Storymaps.




Installation
------------

Uncompress files in the module directory and rename module folder `Storymap`.

Then install it like any other Omeka module and follow the config instructions.



Choose which fields you want the module to use on the storymap by default.

* Item Title: The field you would like displayed for the item’s title in its
  information bubble. The default is `dcterms:title`.
* Item Description: The field you would like displayed for the item’s
  description in its information bubble. The default is `dcterms:description`.
* Item Date: The field you would like to use for item dates on the storymap.
  The default is `dcterms:date`.
* Viewer: The raw json parameters for the viewer, for example to display only
  one row, or to change the scale.

All these parameters can be customized for each storymap.


Usage
-----

Once enabled, the module adds a new block for site pages. Simply select it and
config it (the item pool and eventually the options).

### Add a Storymap Block

Creating a storymap is a two-step process:

1. From the admin panel, edit a page, then click the "Storymap" button in the
  list of new blocks to add.

2. To choose which items appear on your storymap, fill the "Item Pool" form. The
  options are the same than in the config by default (see above).

  ![Storymap Block](https://github.com/Daniel-KM/Omeka-S-module-Storymap/blob/master/data/readme/storymap-block-v3-4.png)

Ready! Open the page.

  ![Storymap Page](https://github.com/Daniel-KM/Omeka-S-module-Storymap/blob/master/data/readme/storymap-page-v3-4.png)



### Parameters of the viewer

Some parameters of the viewer may be customized for each storymap. Currently,
only the `CenterDate` and the `bandInfos` are managed for the Simile storymap.
The default is automatically included when the field is empty.

```javascript
{
    "bandInfos": [
        {
            "width": "80%",
            "intervalUnit": Storymap.DateTime.MONTH,
            "intervalPixels": 100,
            "zoomIndex": 10,
            "zoomSteps": new Array(
                {"pixelsPerInterval": 280, "unit": Storymap.DateTime.HOUR},
                {"pixelsPerInterval": 140, "unit": Storymap.DateTime.HOUR},
                {"pixelsPerInterval": 70, "unit": Storymap.DateTime.HOUR},
                {"pixelsPerInterval": 35, "unit": Storymap.DateTime.HOUR},
                {"pixelsPerInterval": 400, "unit": Storymap.DateTime.DAY},
                {"pixelsPerInterval": 200, "unit": Storymap.DateTime.DAY},
                {"pixelsPerInterval": 100, "unit": Storymap.DateTime.DAY},
                {"pixelsPerInterval": 50, "unit": Storymap.DateTime.DAY},
                {"pixelsPerInterval": 400, "unit": Storymap.DateTime.MONTH},
                {"pixelsPerInterval": 200, "unit": Storymap.DateTime.MONTH},
                {"pixelsPerInterval": 100, "unit": Storymap.DateTime.MONTH} // DEFAULT zoomIndex
            )
        },
        {
            "overview": true,
            "width": "20%",
            "intervalUnit": Storymap.DateTime.YEAR,
            "intervalPixels": 200
        }
    ]
}
```


### Modifying the block template for Storymap

To modify the block template, copy it in your theme (file `view/common/block-layout/storymap.phtml`).

### Modifying the viewer

The template file used to load the storymap is `asset/js/storymap.js`.

You can copy it in your `themes/my_theme/asset/js` folder to customize it. The
same for the default css. See the main [wiki], an [example of use] with Neatline
for Omeka Classic, and the [examples] of customization on the wiki.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


License
-------

This module is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

This software is governed by the CeCILL license under French law and abiding by
the rules of distribution of free software. You can use, modify and/ or
redistribute the software under the terms of the CeCILL license as circulated by
CEA, CNRS and INRIA at the following URL "http://www.cecill.info".

As a counterpart to the access to the source code and rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software's author, the holder of the economic rights, and the
successive licensors have only limited liability.

In this respect, the user's attention is drawn to the risks associated with
loading, using, modifying and/or developing or reproducing the software by the
user in light of its specific status of free software, that may mean that it is
complicated to manipulate, and that also therefore means that it is reserved for
developers and experienced professionals having in-depth computer knowledge.
Users are therefore encouraged to load and test the software's suitability as
regards their requirements in conditions enabling the security of their systems
and/or data to be ensured and, more generally, to use and operate it in the same
conditions as regards security.

The fact that you are presently reading this means that you have had knowledge
of the CeCILL license and that you accept its terms.


Contacts
--------

* Agile Humanities (see [Agile] on GitHub)


Copyright
---------

[Storymap]: https://github.com/Daniel-KM/Omeka-S-module-Storymap
[Omeka S]: https://omeka.org/s
[Scholars’ Lab]: http://scholarslab.org
[fork of NeatlineTime plugin]: https://github.com/Daniel-KM/NeatlineTime
[Omeka Classic]: http://omeka.org
[ISO 8601]: http://www.iso.org/iso/home/standards/iso8601.htm
[Knightlab storymap]: https://storymap.knightlab.com
[example of use]: https://docs.neatline.org/working-with-the-simile-storymap-widget.html
[examples]: http://www.simile-widgets.org/storymap/examples/index.html
[module issues]: https://github.com/agile-humanities/agile-storymap/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[themeing-plugin-pages]: http://omeka.org/codex/Theming_Plugin_Pages "Theming Plugin Pages"
[Scholars’ Lab]: https://github.com/scholarslab
[Agile]: https://github.com/agile-humanities "Agile Humanities"
