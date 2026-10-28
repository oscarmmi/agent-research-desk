<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\SourceDoc;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $documents = [
            ['title' => 'Priority incident response', 'topic' => 'Incident management', 'body' => 'Priority 1 (P1) incidents are acknowledged within 15 minutes, staffed continuously, and escalated to the on-call lead immediately. The incident commander posts an update every 30 minutes until service is restored. The target service restoration time for P1 is four hours. Record the timeline and customer impact in the incident log.'],
            ['title' => 'Service level objectives', 'topic' => 'Service levels', 'body' => 'Harbor Desk operates a 99.9% monthly availability objective for the dispatch platform. The P1 response SLA is a 15-minute acknowledgement and a four-hour restoration target. P2 incidents are acknowledged within one business hour and reviewed during staffed hours.'],
            ['title' => 'Shift handover checklist', 'topic' => 'Shift operations', 'body' => 'At shift change, the outgoing coordinator reviews open incidents, pending escalations, unresolved customer callbacks, and blocked work with the incoming coordinator. Every open item must have an owner, next action, and due time recorded in the handover log.'],
            ['title' => 'Escalation and on-call policy', 'topic' => 'Escalation', 'body' => 'Escalate a P1 immediately to the on-call lead using the incident channel and phone tree. If there is no acknowledgement after ten minutes, contact the operations manager. Document each escalation attempt and time in the incident record.'],
            ['title' => 'Dispatch queue operations', 'topic' => 'Dispatch', 'body' => 'Dispatch coordinators review the active queue every 15 minutes. Items waiting more than 30 minutes are flagged for review. Assign one coordinator as queue owner per shift and note capacity constraints in the shift log.'],
            ['title' => 'Customer update standards', 'topic' => 'Communications', 'body' => 'For active P1 incidents, send a concise status update every 30 minutes, including current impact, work in progress, and the next update time. Avoid unverified estimates. Use plain language and route sensitive questions to the incident commander.'],
            ['title' => 'Daily operations review', 'topic' => 'Reporting', 'body' => 'The daily operations review covers incident volume, response time, resolution trends, queue age, handover quality, and recurring blockers. The operations lead owns follow-up actions and records a due date for each action.'],
            ['title' => 'Dwell time measurement guide', 'topic' => 'Metrics', 'body' => 'Dwell time is the elapsed time an item remains at a single operational stage before moving forward. Measure from the stage-entry timestamp to the stage-exit timestamp, excluding documented system pauses. Review the median and 90th percentile by stage each week.'],
        ];

        foreach ($documents as $document) {
            SourceDoc::query()->updateOrCreate(['title' => $document['title']], $document);
        }

        $terms = [
            ['term' => 'SLA', 'definition' => 'Service level agreement: a documented target for service response or restoration, such as Harbor Desk’s P1 acknowledgement and restoration targets.'],
            ['term' => 'P1', 'definition' => 'Priority 1: a critical incident requiring immediate escalation, continuous staffing, and regular status updates.'],
            ['term' => 'dwell time', 'definition' => 'The elapsed time an item stays in one operational stage, measured from stage entry to stage exit.'],
            ['term' => 'incident commander', 'definition' => 'The person coordinating response activities, decisions, and updates during an active incident.'],
            ['term' => 'handover', 'definition' => 'The structured transfer of open work, ownership, and next actions from one shift to the next.'],
            ['term' => '90th percentile', 'definition' => 'A measurement below which 90% of observed values fall; used to understand slower operational cases.'],
        ];

        foreach ($terms as $term) {
            GlossaryTerm::query()->updateOrCreate(['term' => $term['term']], ['definition' => $term['definition']]);
        }
    }
}
