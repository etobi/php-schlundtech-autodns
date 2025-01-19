# AutoDNS CLI Client

**Version**: 0.0.2

This command-line interface (CLI) client allows you to interact with the SchlundTech AutoDNS API to manage DNS zones and records efficiently.

## Installation

To use this tool, download or clone the repository and make the `autodns.phar` file executable:

```bash
chmod +x bin/autodns.phar
```

## Configuration

Before using the tool, you must generate a configuration file (`autodns.yaml`). Run the following command to create the configuration file:

```bash
bin/autodns.phar config
```

Follow the instructions to set up your credentials and connection details.

## Usage

To display the available commands, use:

```bash
bin/autodns.phar
```

For help on a specific command, use:

```bash
bin/autodns.phar help <command>
```

## Commands

### `zone:info`

Shows detailed information about a zone.

**Usage**:  
`zone:info <zone>`  
Example:  
`bin/autodns.phar zone:info example.com`

---

### `zone:list`

Lists all zones.

**Usage**:  
`zone:list [options]`

**Options**:
- `-l, --list`: Show zones as a simple list
- `-r, --resourcerecords`: Show all resource records for each zone
- `-f, --full-values`: Show full value resource records

Example:  
`bin/autodns.phar zone:list -r`

---

### `zone:record:add`

Adds a resource record to a zone.

**Usage**:  
`zone:record:add <zone> <type> <value> [--name=NAME] [--ttl=TTL] [--pref=PREF]`

**Options**:
- `--name`: The name for the record (subdomain or wildcard)
- `--ttl`: Time-to-live in seconds (default: 600)
- `--pref`: Preference value (for certain record types like MX)

Example:  
`bin/autodns.phar zone:record:add example.com A 1.2.3.4 --name subdomain --ttl 300`

---

### `zone:record:remove`

Removes a resource record from a zone.

**Usage**:  
`zone:record:remove <zone> <type> <value> [--name=NAME] [--ttl=TTL] [--pref=PREF]`

Example:  
`bin/autodns.phar zone:record:remove example.com A 1.2.3.4 --name subdomain`

---

### `zone:record:update`

Updates a resource record in a zone.

**Usage**:  
`zone:record:update <zone> <type> <oldvalue> <newvalue> [--name=NAME] [--ttl=TTL] [--pref=PREF]`

Example:  
`bin/autodns.phar zone:record:update example.com A 1.2.3.4 4.3.2.1 --name subdomain`

---

### `zone:record:searchandreplace`

Replaces a value in all resource records of a given type.

**Usage**:  
`zone:record:searchandreplace <zone> <type> <search> <replace>`

Example:  
`bin/autodns.phar zone:record:searchandreplace example.com A 1.2.3.4 4.3.2.1`

---

### `zone:setmainip`

Sets the main IP address for a zone.

**Usage**:  
`zone:setmainip <zone> <ip> [--ttl=TTL]`

Example:  
`bin/autodns.phar zone:setmainip example.com 1.2.3.4 --ttl 600`

---

### License

This project is licensed under the terms of [MIT License](LICENSE).
