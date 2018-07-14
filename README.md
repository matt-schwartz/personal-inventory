Personal Inventory Web Application
==================================

This is a web app for managing a personal inventory or collection. It's meant to be run on your computer or home network.  This app is great for

- Maintaining a home inventory for insurance purposes
- Keeping track of home electronics and easily ordering more components
- Organizing a coin, stamp, or other collection

There are advantages to using this system over a simple spreadsheet:

- Quickly browse by type or location
- Incorporate photos and images
- Mobile friendly
- Link to online stores for ordering more of an item
- PDF export useful for insurance purposes

Security
--------

There is no included user authentication or other security. This isn't intended to run on the open internet.  *Caveat emptor*.

Photos
------

To take a photo of an item, simply browse to the site on your mobile device.  When editing an item the "upload photo" button will trigger your device to ask if you'd like to use your camera or pick a photo from your camera roll.

Running the Application
-----------------------

The only requirement to run the application is docker.  To run the personal inventory manager on a single desktop computer, run `docker-compose --build up`. Once it's complete open http://localhost in your favorite browser.

For any other type of setup, such as on a home network Linux server, edit or override the settings in `docker-compose.yaml`.

Data Storage
------------

By default data is written to the project's `/data` directory.  This can be changed by editing or overriding the `docker-compose.yaml` file.

Application data is stored using MongoDB in the `/data/db` directory.  Images are stored in the `/data/images` directory.

