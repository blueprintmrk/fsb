main() {
    local NS_PWD=${NS_PWD:-}
    local VOLUMESOCK=""

    if [[ -z $NS_PWD ]]; then
        debug "Using default workdir ($PWD); set \$NS_PWD to override"
        NS_PWD=$PWD
    fi

    checkDocker
    checkPaths

    local NS_UID=$UID
    GID=$(getGid)

    if [[ $(uname) == Darwin ]]
    then
        NS_UID=$(($UID + 2000))
        GID=$(($GID + 2000))
    fi

    socket=$(dockerSocket)

    if [[ $socket == "$HOME/Library/Containers/com.docker.docker/Data/docker.sock" ]]
    then
        VOLUMESOCK="--volume /var/run/docker.sock:/var/run/docker.sock"
    fi

    prefix="$(getInstallPrefix)"
    ns_lib="${prefix}/lib/northstack"

    local DEBUG=${DEBUG:-0}

    if [[ $DEV_MODE == 1 ]]; then

        debug "Running in DEV mode"
        ns_lib=$DEV_SOURCE

        docker run -ti --rm \
            -e DEBUG=$DEBUG \
            -e HOME=$HOME \
            -e NS_PWD="$NS_PWD" \
            -e NS_LIB="$ns_lib" \
            -e NORTHSTACK_UID=$NS_UID \
            -e NORTHSTACK_GID=$GID \
            --volume "$NS_PWD:$NS_PWD" \
            --volume $HOME:$HOME \
            --volume "$socket":"$socket" $VOLUMESOCK \
            --volume "$DEV_SOURCE":/app \
            --init \
            northstack "$@"
    else
        docker run -ti --rm \
            -e DEBUG=$DEBUG \
            -e HOME=$HOME \
            -e NS_PWD="$NS_PWD" \
            -e NS_LIB="$ns_lib" \
            -e NORTHSTACK_UID=$NS_UID \
            -e NORTHSTACK_GID=$GID \
            --volume "$NS_PWD:$NS_PWD" \
            --volume $HOME:$HOME $VOLUMESOCK \
            --volume "$socket":"$socket" \
            --init \
            northstack "$@"
    fi

}
