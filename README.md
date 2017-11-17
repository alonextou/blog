# Installation

`git clone git@github.com:/awc737/blog && cd blog`

### Using Docker:

```
docker build -t awc737/blog .

docker run --name blog -it -p 8001:80 -v `pwd`:/var/www/blog awc737/blog
```

Optionally remove the container when finished:

`docker rm blog`

### Development:

To watch and recompile SASS and JS, from the repository directory:

`npm install -g grunt-cli`

`grunt`
