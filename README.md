HCB - Hotel Control Board
=======

Small project to manage daily hotel duties.

## Quick Install ##
- `git clone https://github.com/primus852/hcb`
- `cd hcb`
- `npm install --prefix=web/assets`
- `composer update`

## Additional ##
- Create user table: `php bin/console doctrine:generate:entities AppBundle && php bin/console doctrine:schema:update --force --complete --dump-sql && php bin/console doctrine:schema:validate`
- Create a user: `php bin/console fos:user:create myusername --super-admin`

## License ##
Copyright (c) 2017 Torsten Wolter MIT license

The MIT License (MIT)

Copyright (c) 2017 Torsten Wolter

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
