import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        me: null
    },
    mutations: {
        me(state, me) {
            state.me = me
        },
        currentUser(state, user) {
            state.me = user
        }
    },
    actions: {
        startupCheck({ commit }) {
            //get jwt
            //getCurrentUser from localstore
            //if jwt and user
            //set current user
            //set jwt
        },
        async signin() {
            //sign in with payload
            //setjwt (handles axios default)
            //get current user
            //return response
        },
        async register() {
            //send register
            //get token from response
            //setJwt(token)
            //getCurrentUser
        },
        logout() {
            //clear current user from localstore
            // clear jwt from localstore
            //delete axios default header
            //clear current user committing null
        },
        async getCurrentUser({ commit }) {
            const { data } = await axios.get('/api/user')
            commit('currentUser', data)
        }
    }
})
