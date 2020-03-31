Run using:
```
php analyzer.php <originalUrl> <diffUrl> [elementId]
```
Optional argument `elementId` assumes default value `make-everything-ok-button` when not passed.

###Examples:

Without `elementId`:
```
php crawler.php https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-0-origin.html https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-4-the-mash.html
```

With `elementId` (in this case the result will be the same as for the previous example, since we are passing the same value as the default):
```
php crawler.php https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-0-origin.html https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-4-the-mash.html make-everything-ok-button
```

With `elementId` different from default one:
```
php crawler.php https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-0-origin.html https://agileengine.bitbucket.io/beKIvpUlPMtzhfAy/samples/sample-4-the-mash.html side-menu
```