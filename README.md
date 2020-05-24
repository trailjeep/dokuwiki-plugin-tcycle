# dokuwiki-plugin-tcycle
A minimalist jQuery slideshow plugin based on Mike Alsup's Javascript-Plugin

Modified version of [DokuWiki](https://www.dokuwiki.org/) [tCycle Plugin](https://www.dokuwiki.org/plugin:tcycle) written by Reinhard Käferböck <rfk [at] kaeferboeck [dot] tk>

Differences:
- Default options set in config manager
- Retrieve images from current or specified namespace while still allowing Manually specified images (default: 1=current namespace)
- Specify width and height of the container to be used as a centered bounding box (default: 600px X 400px)
- Add metadata to images (title for all, caption for JPG) (default: 1=true)
- Add CSS object-fit property (one of: fill, contain ,cover, scale-down, none) (default: contain)

Example (with default values):
```
<tcycle width=600px height=400px speed=500 fx=scroll timeout=4000 metadata=1 fit=contain namespace=1>
{{:image1.jpg|}}
{{:image2.jpg|}}
{{:image3.jpg|}}
...
</tcycle>
```
Or, for all defaults as set in config maager with no additional images:
```
<tcycle></tcycle>
```
