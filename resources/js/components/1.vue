<script>
    export default {
        data() {
            return{messages: []
            }
        },

        mounted() {
            Echo.channel("path").listen("filedownload",({path})=>this.messages.push(path))
        }
    }
</script>

<template>
    <h1>hello </h1>
</template>


