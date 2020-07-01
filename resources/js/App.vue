<template>
<div id="section">
    <b-navbar toggleable="lg" type="dark" variant="info">
        <b-navbar-brand href="#">Call Log</b-navbar-brand>
    </b-navbar>
    <b-container class="bv-example-row">
        <b-row><h3>Call Logs</h3></b-row>
        <b-row>
            <b-col md="6">
                <b-form-group
                label="Per page"
                label-cols-sm="6"
                label-cols-md="4"
                label-cols-lg="3"
                label-align-sm="left"
                label-size="sm"
                label-for="perPageSelect"
                class="mb-0"
                >
                <b-form-select
                    v-model="perPage"
                    id="perPageSelect"
                    size="sm"
                    :options="pageOptions"
                ></b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="6">
                <b-form-group
                label="Status"
                label-cols-sm="6"
                label-cols-md="4"
                label-cols-lg="3"
                label-align-sm="left"
                label-size="sm"
                label-for="perPageSelect"
                class="mb-0"
                >
                <b-form-select
                    v-model="status"
                    id="perPageSelect"
                    size="sm"
                    :options="statusOptions"
                    @change="onStatusChange($event)"
                ></b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="6">
                <b-form-group
                    label="Date Range"
                    label-cols-sm="6"
                    label-cols-md="4"
                    label-cols-lg="3"
                    label-align-sm="left"
                    label-size="sm"
                    label-for="dateRange"
                    class="mb-0"
                    >
                    <date-range-picker
                        id="dateRange"
                        ref="picker"
                        :opens="'center'"
                        :locale-data="{ firstDay: 1, format: 'DD-MM-YYYY' }"
                        v-model="dateRange"
                        @update="updateValues"
                    >
                        <template v-slot:input="picker" style="min-width: 350px;">
                            {{ picker.startDate | date }} - {{ picker.endDate | date }}
                        </template>
                    </date-range-picker>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <p>Showing {{ ((currentPage -1)*perPage)+1 }} to {{ currentPage*perPage }} of total {{ rows }} rows</p>
            <b-table
                id="call-table"
                sticky-header
                striped
                hover
                :busy="isBusy"
                :per-page="perPage"
                :current-page="currentPage"
                :items="calls">
            </b-table>
            <div class="middle">
                <b-pagination
                    v-model="currentPage"
                    :total-rows="rows"
                    :per-page="perPage"
                    aria-controls="call-table"
                >
                </b-pagination>
            </div>
        </b-row>
        <b-row>
            <div id="chart">
                <apexchart
                    v-if="!isBusy"
                    type="area"
                    height="auto"
                    width="100%"
                    :options="chartOptions"
                    :series="series"></apexchart>
                <div v-else>
                    <b-icon icon="three-dots" animation="cylon" font-scale="4"></b-icon>
                </div>
            </div>
        </b-row>
        <b-row></b-row>
    </b-container>
</div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    const FORMAT = 'DD/MM/YYYY';
    export default {
        data() {
            return {
                calls: [],
                perPage: 10,
                currentPage: 1,
                pageOptions: [5, 10, 15],
                status: 'all',
                dateRange: {
                    startDate: moment(),
                    endDate: moment().add(+5, 'days')
                },
                isBusy: false,
                statusOptions: [
                    {value: 'all', text: 'All'},
                    {value: 'in_call', text: 'In Call'},
                    {value: 'hold', text: 'Hold'},
                    {value: 'call_back', text: 'Call Back'},
                    {value: 'do_not_call', text: 'Do Not Call'},
                ],
                series: [
                    {
                    name: "Total Call",
                    data: [
                    ]
                    }
                ],
                chartOptions: {
                    chart: {
                        id: 'yt',
                        type: 'area',
                        height: 360,
                        width: '100%',
                    },
                    colors: ['#00E396'],
                    yaxis: {
                        labels: {
                            minWidth: 40,
                            text: "Call Log",
                        }
                    }
                },
            };
        },
        computed: {
            rows() {
                return this.calls.length
            }
        },
        methods: {
            onStatusChange(event) {
                this.getCallLog();
            },
            updateValues(data){
                this.getCallLog();
            },
            getCallLog() {
                this.isBusy = true;
                axios.get(`/api/call-logs`,{
                    params: {
                        status: this.status,
                        start_date: moment(this.dateRange.startDate).format('YY-MM-DD'),
                        end_date: moment(this.dateRange.endDate).format('YY-MM-DD'),
                    }
                })
                .then(response => {
                    this.isBusy = false;
                    let w = document.getElementById("chart");
                    if(w) {
                        w.style.width = "100%";
                    }
                    // JSON responses are automatically parsed.
                    this.calls = response.data;
                    let data = this.calls
                        .sort((a,b)=> new Date(a.call_date).getTime()- new Date(b.call_date).getTime())
                        .reduce((data, call) => {
                            let date = moment(call.call_date).format(FORMAT);
                            let dateData = data[date];
                            if(!dateData) {
                                data[date] = {
                                    x: date,
                                    y: 0,
                                };
                            }
                            data[date].y ++;
                            return data;
                        }, {});
                    this.series[0].data = Object.values(data);
                    console.log('this.series[0].data', this.series[0].data);
                })
                .catch(e => {
                    this.isBusy = false;
                    console.log(e);
                    this.$bvToast.toast(e, {
                        title: 'Error',
                        autoHideDelay: 5000,
                        appendToast: false,
                    })
                })
            },
        },
        created() {
            this.getCallLog();
        },
        filters: {
            date: function (date) {
                return moment(date).format(FORMAT);
            }
        }
    }
</script>
<style scoped>
.middle {
    width: 100%;
    display: flex;
    justify-content: center;
}
.row {
    margin-top: 10px;
}
.vue-daterange-picker{
    width: 100%;
}
#section {
    background-color: #f8fafc;
}
</style>
