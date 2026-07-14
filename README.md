# Debian Source List Generator

A self-contained PHP web application that generates `/etc/apt/sources.list` files for official Debian repositories. Includes both a web UI and a REST API.

## Features

- Select from 65+ countries with real-world Debian mirrors
- Choose any Debian release (Squeeze through Testing)
- Support for 20+ architectures (amd64, arm64, i386, riscv64, etc.)
- Configurable options: source repos, contrib, non-free, non-free-firmware, security, HTTPS, signed-by
- Generates ready-to-copy CLI commands
- CSRF-protected web form
- REST API for programmatic access

## Requirements

- PHP 8.1+
- No external dependencies

## Usage

### Web UI

Point a PHP-capable web server at the project directory and open `index.php` in your browser.

### API

Send a GET or POST request to `api.php`:

```
GET api.php?mirror=Canada&release=bookworm&arch=amd64&https=yes
```

CURL request via the linux CLI to `api.php`:

```bash
# Basic request
curl -s "http://your-server/api.php?mirror=Germany&release=bookworm"

# With HTTPS and write to sources.list
curl -s "http://your-server/api.php?mirror=United+States&release=bookworm&https=1" | sudo tee /etc/apt/sources.list

# With source repos and HTTPS
curl -s "http://your-server/api.php?mirror=Germany&release=bookworm&src=1&https=1"
```

**API Parameters:**

| Parameter | Description |
|-----------|-------------|
| `mirror` | Country name or full mirror URL |
| `release` | Debian release (e.g. `bookworm`, `trixie`, `testing`) |
| `arch` | Architecture (e.g. `amd64`, `arm64`) |
| `src` | Include deb-src lines (`yes`/`no`) |
| `https` | Use HTTPS (`yes`/`no`) |
| `contrib` | Include contrib (`yes`/`no`) |
| `non-free` | Include non-free (`yes`/`no`) |
| `non-free-firmware` | Include non-free-firmware (`yes`/`no`) |
| `security` | Include security repos (`yes`/`no`) |
| `signed-by` | GPG key path for signed-by |

## Project Structure

```
├── index.php            # Web UI
├── api.php              # REST API
├── styles.css           # Styles
└── lib/
    ├── data.php         # Country, Mirror, Release data models
    └── generator.php    # SourcesListGenerator class
```

## Screenshot

![Debian Sources Generator Interface](screenshot_2026-07-13%2017-59-39.png)

*The web interface showing all generation options configured*

## License

MIT
