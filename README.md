# LfDebate

## Requirements

- ILIAS: 7.x

## Installation

```
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone --branch release_7 https://github.com/leifos-gmbh/LfDebate.git
```

Back in main directory:
```
> composer dump-autoload
```

- Run ILIAS setup update.
- Activate plugin in ILIAS administration.
