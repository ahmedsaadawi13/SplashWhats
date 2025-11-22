<?php
// FILE: /app/models/Plan.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Plan Model
 * Handles subscription plans with limits
 */
class Plan extends Model
{
    protected $table = 'plans';

    /**
     * Get all active plans
     * @return array
     */
    public function getActivePlans()
    {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Create plan
     * @param array $data
     * @return int Plan ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Get plan with limits parsed
     * @param int $planId
     * @return array|false
     */
    public function getWithLimits($planId)
    {
        $plan = $this->findById($planId);

        if ($plan && !empty($plan['limits'])) {
            $plan['limits'] = json_decode($plan['limits'], true);
        }

        return $plan;
    }
}
