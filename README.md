# Munin module for Icinga Web 2

#### Table of Contents

1. [About](#about)
2. [License](#license)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [Configuration](#configuration)

## About

This module integrates [Munin](https://github.com/munin-monitoring/munin) into [Icinga Web 2](https://github.com/Icinga/icingaweb2).

## License

Munin and this Icinga Web 2 module are licensed under the terms of the GNU General Public License Version 2, you will find a copy of this license in the LICENSE file included in the source package.

The module adopts the same license as Munin, having copied some CSS from Munin.

## Requirements

This module glues Munin and Icinga Web 2 together. Both of them are required
to be installed and configured:

* [Icinga Web 2](https://github.com/Icinga/icingaweb2)
* [Munin](https://github.com/munin-monitoring/munin)

## Installation

Extract this module to your Icinga Web 2 modules directory in the `munin` directory.

```
mkdir -p /usr/local/share/icingaweb2/modules
cd /usr/local/share/icingaweb2/modules
git clone https://git.linuxminded.nl/git/icingaweb2-module-munin.git munin
```

### Enable Icinga Web 2 module

Enable the module in the Icinga Web 2 frontend in `Configuration -> Modules -> munin -> State`.
You can also enable the module by using the `icingacli` command:

```
icingacli module enable munin
```

## Configuration

### Munin Configuration

The base URL for Munin (e.g. `/munin`) must be accessible on the webserver.
When using `graph_strategy cgi` the base URL for `munin-cgi-graph` must also be accessible.

### Module Configuration

Install the `config.ini` file in the module configuration directory.

```
cd /usr/local/share/icingaweb2/modules/munin
mkdir -p /etc/icingaweb2/modules/munin
cp etc/config.ini /etc/icingaweb2/modules/munin/
```

Adjust the content of the configuration file to match your Munin setup.

When not using cron to generate the graphs, make sure to change `graph_strategy` to `cgi`.

