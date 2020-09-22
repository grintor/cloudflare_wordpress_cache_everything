# cloudflare_wordpress_cache_everything
A method to cache and entire wordpress site in cloudflare.


Previously, the only way to have an wordpress site entirely cached by Cloudflare was by using the instructions [here](https://support.cloudflare.com/hc/en-us/articles/236166048) which require a Cloudflare business plan for $200/month. In this repo are the parts needed to allow any wordpress site to be fully cached using the Cloudflare free tier which means that even a Godaddy shared-hosting wordpress site can serve millions of users per day with no lag.



The basic idea here is that there is the cration of a subdomain which hosts the real Wordpress site, (cms.example.com) and a subdomain which hosts a reverse proxy to the real wordpress site which rewrites all the URLs in the site html and javascript to point to itself on the fly (www.example.com). To prevent an SSO nightmare, the cms site is configured to block WebCrawlers, but the proxy removes those restrictions on the fly as well. Then Cloudflare is placed in front of the www site and configured using a page rule to cache everything at www.example.com/*



Cloudflare and the proxy can pass-though POST and PUT requests and only cache GETs, so the site is not entirely static and web forms and ajax should still be able to work the same.
