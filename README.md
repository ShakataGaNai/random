one-offs
========

A bunch of random (and not really connected) one offs

#### cloudflare-banhammer
See https://snowulf.com/2014/12/22/using-cloudflare-as-a-banhammer/

#### awsR53update
Rather than rely on a dynamic DNS service, I built my own in AWS Route 53. The
script checks the current IP against the DNS, and updates AWS as nessisary.
This is not required for the gallery to work, however it is required if you 
want an easy way to connect to ownCloud over the LAN.
