#!/usr/bin/env sh

# Wrapper for running the northstack cli in docker
# Handles uid mapping, and given access to the docker sock so northstack can start local dev


echo "Running DEV northstack source in SOURCE"

if [[ -S /var/lib/docker.sock ]]; then
    socket=/var/lib/docker.sock
elif [[ -S $HOME/Library/Containers/com.docker.docker/Data/docker.sock ]]; then
    # the control socket likes to hide here on OSX
    socket=$HOME/Library/Containers/com.docker.docker/Data/docker.sock
else
    echo "Error: no docker control socket found. Is docker installed and running?"
    exit 1
fi

docker run -ti --rm \
    -e DEBUG=$DEBUG \
    --user=$UID --userns=host \
    --volume "$(pwd)":/current \
    --volume $HOME:$HOME \
    --volume /etc/passwd:/etc/passwd \
    --volume /var/lib/docker.sock:/var/lib/docker.sock \
    --volume SOURCE:/app \
    --init \
    northstack "$@"