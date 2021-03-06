<?php

namespace OpenDominion\Http\Controllers\Dominion;

use Exception;
use OpenDominion\Calculators\Dominion\Actions\ExplorationCalculator;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Helpers\LandHelper;
use OpenDominion\Http\Requests\Dominion\Actions\ExploreActionRequest;
use OpenDominion\Services\Analytics\AnalyticsEvent;
use OpenDominion\Services\Analytics\AnalyticsService;
use OpenDominion\Services\Dominion\Actions\ExploreActionService;
use OpenDominion\Services\Dominion\Queue\ExplorationQueueService;

class ExplorationController extends AbstractDominionController
{
    public function getExplore()
    {
        return view('pages.dominion.explore', [
            'explorationCalculator' => app(ExplorationCalculator::class),
            'explorationQueueService' => app(ExplorationQueueService::class),
            'landCalculator' => app(LandCalculator::class),
            'landHelper' => app(LandHelper::class),
        ]);
    }

    public function postExplore(ExploreActionRequest $request)
    {
        $dominion = $this->getSelectedDominion();
        $explorationActionService = app(ExploreActionService::class);

        try {
            $result = $explorationActionService->explore($dominion, $request->get('explore'));

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors([$e->getMessage()]);
        }

        // todo: fire laravel event
        $analyticsService = app(AnalyticsService::class);
        $analyticsService->queueFlashEvent(new AnalyticsEvent(
            'dominion',
            'explore',
            '', // todo: make null?
            array_sum($request->get('explore'))
        ));

        $request->session()->flash('alert-success', $result['message']);
        return redirect()->route('dominion.explore');
    }
}
