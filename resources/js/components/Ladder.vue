<template>
    <div>
        <h1 class="text-white mb-6">{{ this.league }}:</h1>
        {{ error }}
        <loading v-if="loading" class="loading-spinner"></loading>
        <table class="table table-striped text-white" v-if="!loading">
            <thead>
                <tr>
                    <th scope="col">Rank</th>
                    <th scope="col">Name</th>
                    <th scope="col">Account name</th>
                    <th scope="col">Main skill</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="player in this.sortLadder()">
                    <th scope="row">{{ player.rank }}</th>
                    <td>{{ player.character.name }}</td>
                    <td>{{ player.account.name }}</td>
                    <td v-if="player.skills.length !== 0">{{ player.skills.mainSkill.typeLine }}</td>
                    <td v-else class="text-red">Private or Unknown</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-center align-items-center text-2xl" v-if="!loading">
            <button @click="prevPage"><span class="mdi mdi-chevron-left mr-2 text-white"></span></button>
            <button @click="nextPage"><span class="mdi mdi-chevron-right ml-2 text-white"></span></button>
        </div>

    </div>
</template>

<script>
    import Loading from 'vue-loading-spinner/src/components/Circle3';

    export default {
        components: {
            Loading
        },

        props: ['league'],

        data() {
            return {
                pageSize: 15,
                currentPage: 1,
                ladder: {},
                loading: true,
                error: ''
            }
        },

        created: function () {
            if (this.league != null) {
                this.getLadder(this.league);
            } else {
                this.getLadder('Betrayal');
            }
        },

        methods: {
            getLadder: function (league) {
                this.loading = true;

                axios.get('/api/ladder?league=' + league)
                    .then(response => {
                            this.loading = false;
                            this.ladder = JSON.parse(response.data);
                        }
                    ).catch(error => {
                        console.log(error);
                    });
            },

            sortLadder: function () {
                if (this.ladder.entries != null) {
                    return this.ladder.entries.filter((row, index) => {
                        let start = (this.currentPage-1)*this.pageSize;
                        let end = this.currentPage*this.pageSize;
                        if(index >= start && index < end) return true;
                    })
                } else {
                    return null
                }
            },

            nextPage: function () {
                if ( (this.currentPage*this.pageSize) < this.ladder.entries.length) this.currentPage++;
            },

            prevPage: function () {
                if(this.currentPage > 1) this.currentPage--;
            }
        },
    }
</script>
