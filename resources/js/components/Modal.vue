<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container bg-indigo-darkest">

                    <div class="modal-body">
                        <slot name="body">
                            <form method="get">
                                <vue-simple-suggest v-model="chosen"
                                                    :list="simpleSuggestionList"
                                                    :filter-by-query="true"
                                                    name="league"
                                                    autocomplete="off"
                                                    placeholder="E.g. Zizarans Shaper Race (PL3219)"
                                                    class="mb-3"></vue-simple-suggest>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-black hover:bg-grey-darkest text-white font-bold py-2 px-4 rounded mr-3">Find</button>
                                    <button type="button" class="bg-black hover:bg-grey-darkest text-white font-bold py-2 px-4 rounded" @click="$emit('close')">Close</button>
                                </div>
                            </form>

                        </slot>
                    </div>

                    <!--<div class="modal-footer">
                        <slot name="footer">
                            <button class="modal-default-button" @click="$emit('close')">
                                OK
                            </button>
                        </slot>
                    </div>-->
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import VueSimpleSuggest from 'vue-simple-suggest'

    export default {
        name: "Modal",
        components: {
            VueSimpleSuggest
        },
        data() {
            return {
                chosen: '',
                leagues: []
            }
        },

        created: function () {
            this.createSuggestionList();
        },

        methods: {
            createSuggestionList: function() {
                axios.get('/api/leagues').then(response => {
                    let leagueArray = [];

                    for (let league in response.data) {
                        leagueArray.push(response.data[league].league);
                    }

                    this.leagues = leagueArray;
                });
            },

            simpleSuggestionList: function() {
                console.log(this.leagues);
                return this.leagues;
            }
        }

    }
</script>

<style scoped>
    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }

    .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }

    .modal-container {
        width: 600px    ;
        margin: 0px auto;
        padding: 20px 30px;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
    }

    /*
     * The following styles are auto-applied to elements with
     * transition="modal" when their visibility is toggled
     * by Vue.js.
     *
     * You can easily play with the modal transition by editing
     * these styles.
     */

    .modal-enter {
        opacity: 0;
    }

    .modal-leave-active {
        opacity: 0;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }
</style>
